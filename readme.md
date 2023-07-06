# INCI base #
**Contributors**: natata7
**Tags**: inci, ingredients, cosmetics, make-up
**Requires at least**: 5.2
**Tested up to**: 6.2
**Requires PHP**: 7.4
**License**: GPLv3 or later
**License URI**: https://www.gnu.org/licenses/gpl-3.0.html

## Description ##

A WordPress plugin to manage INCI (International Nomenclature of Cosmetic Ingredients). You can set up your database of ingredients and products and easily insert a product table into posts and pages using a shortcode.

## Features ##

* Custom Post Type Ingredient: it comes with a function list, a source list and a visual safety field.
* Custom Post Type Product: it comes with a brand taxonomy.
* Single and multiple search for ingredients: check the ingredient against the local database.
* Options: possibility to exclude the default CSS, copy it into your style.css and customize it; change the disclaimer content.
* Shortcode: in the product list, there is a column where you can copy the 'basic' shortcode relative to a specific product.
If you need a different way to display it, you can:

    1. specify a different title
    Example: `[wp_inci_product id="33591" title="My custom title"]`
    2. automatically insert the product permalink
    Example: `[wp_inci_product id="33591" link="true"]`
    3. remove the ingredients listing
    Example: `[wp_inci_product id="33591" link="true" list="false"]`
    4. remove the safety from ingredients listing
    Example: `[wp_inci_product id="33591" safety="false"]`

## Credits ##
* [CMB2](https://en-gb.wordpress.org/plugins/cmb2/) by [CMB2 team](https://cmb2.io/)
* [Extended CPTs](https://github.com/johnbillion/extended-cpts) by [John Blackbourn](https://johnblackbourn.com/)
* [Carbon Fields](https://github.com/htmlburger/carbon-fields) by [htmlBurger](https://htmlburger.com/)
