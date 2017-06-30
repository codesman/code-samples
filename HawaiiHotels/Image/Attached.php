<?php
namespace HawaiiAloha\PostType\HawaiiHotels\Image;

final class Attached {
	
	/**
	 * Attached constructor.
	 */
	public function __construct() {

		add_action( 'admin_print_footer_scripts', [ $this, 'js' ] );
		add_action( 'wp_ajax_delete_attached_image', [ $this, 'deleteImage' ] );
		add_action( 'wp_ajax_delete_all_attached', [ $this, 'deleteAll' ] );
	}

	/**
	 * AJAX function to handle removing currently attached images
	 */
	public function js() {
		global $pagenow;
		global $post_type;

		if ( 'hawaii-hotels' === $post_type && 'post.php' === $pagenow ) {
			?>
			<script type="text/javascript">
				
				jQuery(document).ready(function ($) {

					$(".remove-attachment").click(function (event) {

						event.preventDefault();

						$(this).html("<div class='spinner is-active'><div>");

						$.post(ajaxurl, {

							_ajax_nonce: "<?php echo wp_create_nonce( 'ajax_action_delete_attached_image' ); ?>",
							action: "delete_attached_image",
							attachment_id: this.name

						}, function (response) {

							var imageId = "#image-" + response.data.attachment_id;

							$("#total-attached-images").html("?");
							$(imageId).hide();
						});
					});

					$(".remove-all-attachments").click(function (event) {

						event.preventDefault();

						$(this).html("<div class='spinner is-active'><div>");

						$.post(ajaxurl, {

							_ajax_nonce: "<?php echo wp_create_nonce( 'ajax_action_delete_all_attached' ); ?>",
							action: "delete_all_attached",
							post_id: <?php echo $_GET['post']; ?>

						}, function (response) {
							
							window.location.reload(true);
						});
					});
				});
			</script>
			<?php
		}
	}


	/**
     * Function to delete all of the currently attached images
     * 
	 * @return mixed
	 */
	public function deleteAll() {

		$isValid = check_ajax_referer( 'ajax_action_delete_all_attached', false, false );

		if ( ! $isValid ) {

			return wp_send_json_error( "Invalid Nonce" );
		}

		$postID = $_POST["post_id"] ?? '';

		$response = [
			'post_id' => $postID,
		];

		$images = get_attached_media('image', $postID);
		
		foreach ($images as $k => $image){

			$deleted = wp_delete_attachment( $image->ID );
			
			if(is_wp_error($deleted)){
				
				$response["message"] = "There was an error, the image was not deleted";
				
				return wp_send_json_error( $response );
			}
		}
		
		$response["message"] = "All attachments were removed";

		return wp_send_json_success( $response );
	}

	/**
     * Deletes one specific attached image
     * 
	 * @return mixed
	 */
	public function deleteImage() {

		$isValid = check_ajax_referer( 'ajax_action_delete_attached_image', false, false );

		if ( ! $isValid ) {

			return wp_send_json_error( "Invalid Nonce" );
		}

		$attachmentid = $_POST["attachment_id"] ?? false;
		$force_delete = $_POST["force_delete"] ?? false;

		$response = [
			'attachment_id' => $attachmentid,
		];


		if ( false === $attachmentid ) {

			return wp_send_json_error( "Attachment not deleted" );

		} else {

			$deleted = wp_delete_attachment( $attachmentid, $force_delete );

			if ( false === $deleted ) {

				$response["message"] = "There was an error. Attachment not deleted";

				return wp_send_json_error( $response );

			} else {

				$this->maybeRemoveFeaturedImage( $_GET['post'], $attachmentid );

				$response["message"] = "Deleted!";

				return wp_send_json_success( $response );
			}
		}
	}


	/**
     * If the image to be deleted is the Featured Image, remove it's ID from the post meta
     * 
	 * @param $postId
	 * @param $imageId
	 */
	private function maybeRemoveFeaturedImage( $postId, $imageId ) {

		$featuredImage = get_post_meta( $postId, '_thumbnail_id', true );

		if ( $imageId == $featuredImage ) {

			delete_post_meta( $postId, '_thumbnail_id' );
		}
	}
}
