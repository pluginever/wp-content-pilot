<?php
/**
 * Get all amazon departments
 *
 * @since 1.0.0
 *
 * @return mixed
 */
function wpcp_get_amazon_categories($index = null ) {

    $cats =  [
        'All',
        'Apparel',
        'Appliances',
        'Automotive',
        'Baby',
        'Beauty',
        'Blended',
        'Books',
        'Classical',
        'DVD',
        'Electronics',
        'Grocery',
        'HealthPersonalCare',
        'HomeGarden',
        'HomeImprovement',
        'Jewelry',
        'KindleStore',
        'Kitchen',
        'Lighting',
        'Marketplace',
        'MP3Downloads',
        'Music',
        'MusicTracks',
        'MusicalInstruments',
        'OfficeProducts',
        'OutdoorLiving',
        'Outlet',
        'PetSupplies',
        'PCHardware',
        'Shoes',
        'Software',
        'SoftwareVideoGames',
        'SportingGoods',
        'Tools',
        'Toys',
        'VHS',
        'Video',
        'VideoGames',
        'Watches'
    ];

    if( $index !==  null ){
        return $cats[$index];
    }
    return $cats;
}
