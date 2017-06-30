# Processing scripts for Expedia Affiliate Network Hotel Images

## IMPORTANT - These scripts should be placed in an admin user home directory and run from there - NOT in any publicly available directory. They are meant to be triggered manually or by cronjob only.

### By default, these scripts are configured to be run as mysql root user without a password.  You may need to change this depending on config.

We are using images from Expedia, but we cannot hotlink to their servers so we have to download any images we want to our server.

Their default image size is small, but most of them have a large @1000px size and we need to check each image and remove it from the list if not available in the large size.

Expedia provides 2 master lists, the ActivePropertyList and the HotelImageList.

To get the images we need, a number of processes are required:

* Download the ActivePropertyList file from Expedia
* Query the WordPress Database for EANHotelIDs that we have published
* Filter the ActivePropertyList for Hawaii Properties
* Download HotelImageList from Expedia
* Build a list of all images for the Properties that we handle
* Filter that list for all images that have large sizes

Once we have a table of only full sized images, then we can use the WordPress interface to download/attach them to each specific Hotel.

We also use the filtered list of Active Hawaii Properties for display in WP Admin when a new property is to be added and also to make the hotel meta info available in the Hotel Edit Page.

You can trigger the `update_expedia_images.php` script manually, or with a cronjob.  The plan is to run it daily in off hours.

The prepare processes are relatively quick and should finish within a few minutes. The verify_large_images process is long running.  It requests headers for every image that Expedia gives a URL for(@9000 in this case). When I run it in the dev environment, it takes over an hour. I found that this check was necessary because many of the URLs were not actually returning valid images.
