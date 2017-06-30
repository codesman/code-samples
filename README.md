# Tom Holland - Code Samples

## Hawaii Hotels
This code sample is just a small piece of a very large custom WordPress app that I am working on for Hawaii Aloha Travel.  It is a Custom Post Type for Hotels that features integration of Expedia Affiliate Network Hotel Images.  This provides an interface for the admin user to see which images Expedia has available for each Hotel, as well as all of the images that are currently associated with the Hotel and an easy way to add/replace images as Expedia updates their data or a new Hotel is added.

## Expedia Hotel Image Scripts
The Expedia Affiliate Network does not provide a restful API for accessing their Hotel Image URLs.  They ony provide csv dumps of their entire database that must be parsed for what you need.  Hawaii Aloha Travel needs to have only the image URLs for the hotels that they book. This is a set of scripts that parses the Expedia data for the URLs that we need and stores the result in a database that is used by the custom WordPress functionality that is mentioned above in the Hawaii Hotels code.

## CheckFront Bundles
Hawaii Jeep Tours uses the CheckFront booking engine. The owner wanted to be able to offer multi-island bundles with discounts, but the CheckFront booking engine does not have the ability to create bundles in the way he wanted, so this plugin was created to enable customers to book multi-island bundles.

Can be seen here: [https://jeeptourshawaii.com/oahu-pearl-harbor-super-saver-combo/](https://jeeptourshawaii.com/oahu-pearl-harbor-super-saver-combo/)
