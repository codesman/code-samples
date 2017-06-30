<?php
namespace HawaiiAloha\PostType\HawaiiHotels\Image;

final class Sideload {

	/**
	 * Sideload constructor.
	 */
	public function __construct() {

		add_action( 'admin_print_footer_scripts', [ $this, 'js' ] );
		add_action( 'wp_ajax_attach_sideload_image', [ $this, 'attach' ] );
		add_action( 'wp_ajax_sideload_featured_image', [ $this, 'attach' ] );
	}

	/**
	 * AJAX functions to attach Expedia Images to Hotel Posts
	 */
	public function js() {
		global $pagenow;
		global $post_type;

		if ( 'hawaii-hotels' === $post_type && 'post.php' === $pagenow ) {
			?>
			<script type="text/javascript">
				
				jQuery(document).ready(function ($) {

					$(".attach-image").click(function (event) {

						event.preventDefault();

						info = $(this).closest("td");

						$(this).html("<div class='spinner is-active'><div>");

						$.post(ajaxurl, {

							_ajax_nonce: "<?php echo wp_create_nonce( 'ajax_action_attach_sideload_image' ); ?>",
							action: "attach_sideload_image",
							source_url: info.find('.image-source-url')[0].innerText,
							filename: info.find('.image-filename')[0].innerText,
							caption: info.find('.image-caption')[0].innerText,
							alt: info.find('.image-alt')[0].innerText,
							post_id: <?php echo $_GET['post']; ?>

						}, function (response) {

							info
								.find('.image-status .attach-image')
								.html("Attached!")
								.removeClass('button-primary')
								.addClass('button-success');
						});
					});

					$(".attach-featured-image").click(function (event) {

						event.preventDefault();

						info = $(this).closest("td");

						$(this).html("<div class='spinner is-active'><div>");

						$.post(ajaxurl, {

							_ajax_nonce: "<?php echo wp_create_nonce( 'ajax_action_attach_sideload_image' ); ?>",
							action: "sideload_featured_image",
							source_url: info.find('.image-source-url')[0].innerText,
							filename: info.find('.image-filename')[0].innerText,
							caption: info.find('.image-caption')[0].innerText,
							alt: info.find('.image-alt')[0].innerText,
							featured: true,
							post_id: <?php echo $_GET['post']; ?>

						}, function (response) {

							info
								.find('.image-status .attach-featured-image')
								.html("Attached!")
								.removeClass('button-primary')
								.addClass('button-success');
							
							window.location.reload(true);
						});
					});
				});
			</script>
			<?php
		}
	}

	/**
     * Sideloads & attaches the selected Expedia Image to the Hotel Post.
     * 
	 * @return mixed
	 */
	public function attach() {

		$isValid = check_ajax_referer( 'ajax_action_attach_sideload_image', false, false );

		if ( ! $isValid ) {

			return wp_send_json_error( "Invalid Nonce" );
		}


		$postId    = $_POST["post_id"] ?? '';
		$sourceUrl = $_POST["source_url"] ?? '';
		$filename  = $_POST["filename"] ?? '';
		$caption   = $_POST["caption"] ?? '';
		$alt       = $_POST["alt"] ?? '';
		$featured  = $_POST["featured"] ?? false;


		$response = [
			'filename'   => $filename,
			'source_url' => $sourceUrl,
			'caption'    => $caption,
			'alt'        => $alt,
			'post_id'    => $postId,
			'featured' => $featured,
		];


		if ( '' === $filename ) {

			return wp_send_json_error( "Image not attached" );

		} else {

			$attached = $this->handle( $sourceUrl, $postId, $filename, $caption, $alt, $featured );

			if ( is_wp_error( $attached ) ) {

				$response["message"] = "There was an error. Image could not be attached";

				return wp_send_json_error( $response );

			} else {

				$response["message"] = "Attached!";

				return wp_send_json_success( $response );
			}
		}
	}


	/**
     * Handles Sideloading of the Expedia Hotel Image
     * 
	 * @param $sourceUrl
	 * @param $postId
	 * @param $filename
	 * @param $caption
	 * @param $alt
	 *
	 * @return \WP_Error|void
	 */
	public function handle( $sourceUrl, $postId, $filename, $caption, $alt, $featured = false ) {

		$url = $sourceUrl;
		$tmp = download_url( $url );
		if ( is_wp_error( $tmp ) ) {
			// download failed, handle error
		}

		$attachTo  = $postId;
		$fileArray = [];

		// Set variables for storage
		$fileArray['name']     = $filename;
		$fileArray['tmp_name'] = $tmp;

		// If error storing temporarily, unlink
		if ( is_wp_error( $tmp ) ) {
			@unlink( $fileArray['tmp_name'] );
			$fileArray['tmp_name'] = '';
		}

		// do the validation and storage stuff
		$id = media_handle_sideload( $fileArray, $postId );

		// If error storing permanently, unlink
		if ( is_wp_error( $id ) ) {
			@unlink( $fileArray['tmp_name'] );

			return $id;
		}

		$postArray = [
			'ID'           => $id,
			'post_content' => $url,
			'post_excerpt' => $caption,
		];

		wp_update_post( $postArray );

		update_post_meta( $id, '_wp_attachment_image_alt', $alt );

		if ( $featured ) {
			update_post_meta( $postId, '_thumbnail_id', $id );
		}
	}
}
