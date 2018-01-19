<?php

namespace Pluginever\WPCP;
class Install {

    public function __construct() {
        register_activation_hook( WPCP_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( WPCP_FILE, array( $this, 'deactivate' ) );
    }

    public function activate() {
        $current_db_version   = get_option( 'wpcp_db_version', null );
        $current_wpcp_version = get_option( 'wpcp_version', null );
        $this->create_tables();
        $this->populate_data();
        $this->create_cron_jobs();

        //save db version
        if ( is_null( $current_wpcp_version ) ) {
            update_option( 'wpcp_version', WPCP_VERSION );
        }

        //save db version
        if ( is_null( $current_db_version ) ) {
            update_option( 'wpcp_db_version', '1.00' );
        }

        //save install date
        if ( false == get_option( 'wpcp_install_date' ) ) {
            update_option( 'wpcp_install_date', current_time( 'timestamp' ) );
        }

    }

    public function create_tables() {
        global $wpdb;

        $collate = '';
        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty( $wpdb->charset ) ) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }
            if ( ! empty( $wpdb->collate ) ) {
                $collate .= " COLLATE $wpdb->collate";
            }

        }

        $table_schema = [
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpcp_links` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `camp_id` int(11) NOT NULL,
                `url` varchar(255) NOT NULL,
                `keyword` varchar(255) DEFAULT NULL,
                `camp_type` varchar(255) DEFAULT NULL,
                `status` tinyint(1) unsigned DEFAULT '0',
                `identifier` TEXT DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                  UNIQUE (url)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpcp_posts` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `camp_id` int(11) NOT NULL,
                `post_id` int(11) NOT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                 `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpcp_logs` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `camp_id` int(11) NOT NULL,
                `keyword` varchar(255) NOT NULL DEFAULT '',
                `message` varchar(255) DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpcp_data` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `data_type` varchar(255) NOT NULL DEFAULT '',
                `data_id` varchar(255) NOT NULL DEFAULT '',
                `data` varchar(255) NOT NULL DEFAULT '',
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                 `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) $collate;",
        ];

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }

    }

    /**
     * Populate tables with initial data
     *
     * @return void
     */
    public function populate_data() {
        global $wpdb;

        // check if click bank category exist else insert
        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}wpcp_data` WHERE data_type='click_bank_cateogry'" ) ) {
            $sql = "INSERT INTO `{$wpdb->prefix}wpcp_data` (`data_type`, `data_id`, `data`)
                    VALUES ('click_bank_category','1253', 'Arts & Entertainment'), 
                    ('click_bank_category','1510', 'Arts & Entertainment'), 
                    ('click_bank_category','1266', 'Betting Systems'), 
                    ('click_bank_category','1283', 'Computers / Internet'), 
                    ('click_bank_category','1297', 'Cooking, Food & Wine'), 
                    ('click_bank_category','1308', 'E-business & E-marketing'), 
                    ('click_bank_category','1362', 'Education'), 
                    ('click_bank_category','1332', 'Employment & Jobs'), 
                    ('click_bank_category','1338', 'Fiction'), 
                    ('click_bank_category','1340', 'Games'), 
                    ('click_bank_category','1344', 'Green Products'), 
                    ('click_bank_category','1347', 'Health & Fitness'), 
                    ('click_bank_category','1366', 'Home & Garden'), 
                    ('click_bank_category','1377', 'Languages'), 
                    ('click_bank_category','1392', 'Mobile'), 
                    ('click_bank_category','1400', 'Parenting & Families'), 
                    ('click_bank_category','1408', 'Politics / Current Events'), 
                    ('click_bank_category','1410', 'Reference'), 
                    ('click_bank_category','1419', 'Self-Help'), 
                    ('click_bank_category','1432', 'Software & Services'), 
                    ('click_bank_category','1461', 'Spirituality, New Age & Alternative Beliefs'), 
                    ('click_bank_category','1472', 'Sports'), 
                    ('click_bank_category','1494', 'Travel');";
            $wpdb->query( $sql );
        }

        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}wpcp_data` WHERE data_type='envato_platform'" ) ) {
            $sql = "INSERT INTO `{$wpdb->prefix}wpcp_data` (`data_type`, `data_id`, `data`)
                    VALUES ( 'envato_platform', 'themeforest.net','ThemeForest'),
                    ('envato_platform', 'codecanyon.net','CodeCanyon'),
                    ('envato_platform', 'photodune.net','PhotoDune'),
                    ('envato_platform', 'videohive.net','VideoHive'),
                    ('envato_platform', 'graphicrever.net','GraphicsRever'),
                    ('envato_platform', '3docean.net','3DOcean');";
            $wpdb->query( $sql );
        }

        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}wpcp_data` WHERE data_type='amazon_category'" ) ) {
            $sql = "INSERT INTO `{$wpdb->prefix}wpcp_data` (`data_type`, `data_id`, `data`)
                VALUES ('amazon_category','All', 'All'), 
                    ('amazon_category','Apparel', 'Apparel'), 
                    ('amazon_category','Appliances', 'Appliances'), 
                    ('amazon_category','Automotive', 'Automotive'), 
                    ('amazon_category','Baby', 'Baby'), 
                    ('amazon_category','Beauty', 'Beauty'), 
                    ('amazon_category','Blended', 'Blended'), 
                    ('amazon_category','Books', 'Books'), 
                    ('amazon_category','Classical', 'Classical'), 
                    ('amazon_category','DVD', 'DVD'), 
                    ('amazon_category','Electronics', 'Electronics'), 
                    ('amazon_category','Grocery', 'Grocery'), 
                    ('amazon_category','HealthPersonalCare', 'HealthPersonalCare'), 
                    ('amazon_category','HomeGarden', 'HomeGarden'), 
                    ('amazon_category','HomeImprovement', 'HomeImprovement'), 
                    ('amazon_category','Jewelry', 'Jewelry'), 
                    ('amazon_category','KindleStore', 'KindleStore'), 
                    ('amazon_category','Kitchen', 'Kitchen'), 
                    ('amazon_category','Lighting', 'Lighting'), 
                    ('amazon_category','Marketplace', 'Marketplace'), 
                    ('amazon_category','MP3Downloads', 'MP3Downloads'), 
                    ('amazon_category','Music', 'Music'), 
                    ('amazon_category','MusicTracks', 'MusicTracks'), 
                    ('amazon_category','MusicalInstruments', 'MusicalInstruments'), 
                    ('amazon_category','OfficeProducts', 'OfficeProducts'), 
                    ('amazon_category','OutdoorLiving', 'OutdoorLiving'), 
                    ('amazon_category','Outlet', 'Outlet'), 
                    ('amazon_category','PetSupplies', 'PetSupplies'), 
                    ('amazon_category','PCHardware', 'PCHardware'), 
                    ('amazon_category','Shoes', 'Shoes'), 
                    ('amazon_category','Software', 'Software'), 
                    ('amazon_category','SoftwareVideoGames', 'SoftwareVideoGames'), 
                    ('amazon_category','SportingGoods', 'SportingGoods'), 
                    ('amazon_category','Tools', 'Tools'), 
                    ('amazon_category','Toys', 'Toys'), 
                    ('amazon_category','VHS', 'VHS'), 
                    ('amazon_category','Video', 'Video'), 
                    ('amazon_category','VideoGames', 'VideoGames'), 
                    ('amazon_category','Watches', 'Watches');";
            $wpdb->query( $sql );
        }

        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}wpcp_data` WHERE data_type='amazon_sites'" ) ) {
            $sql = "INSERT INTO `{$wpdb->prefix}wpcp_data` (`data_type`, `data_id`, `data`)
                    VALUES ('amazon_sites','us', 'amazon.com'), 
                    ('amazon_sites','uk', 'amazon.co.uk'), 
                    ('amazon_sites','ca', 'amazon.ca'), 
                    ('amazon_sites','de', 'amazon.de'), 
                    ('amazon_sites','fr', 'amazon.fr'), 
                    ('amazon_sites','it', 'amazon.it'), 
                    ('amazon_sites','es', 'amazon.es'), 
                    ('amazon_sites','cn', 'amazon.cn'), 
                    ('amazon_sites','in', 'amazon.in'), 
                    ('amazon_sites','js', 'amazon.co.js'), 
                    ('amazon_sites','br', 'amazon.com.br'), 
                    ('amazon_sites','mx', 'amazon.com.mx');";
            $wpdb->query( $sql );
        }

        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}wpcp_data` WHERE data_type='ebay_site'" ) ) {
            $sql = "INSERT INTO `{$wpdb->prefix}wpcp_data` (`data_type`, `data_id`, `data`)
                    VALUES ('ebay_site','EBAY-US', 'eBay United States'), 
                    ('ebay_site','EBAY-ENCA', 'eBay Canada'), 
                    ('ebay_site','EBAY-GB', 'eBay UK'), 
                    ('ebay_site','EBAY-AU', 'eBay Australia'), 
                    ('ebay_site','EBAY-AT', 'eBay Austria'), 
                    ('ebay_site','EBAY-FR', 'eBay France'), 
                    ('ebay_site','EBAY-DE', 'eBay Germany'), 
                    ('ebay_site','EBAY-MOTOR', 'eBay Motors'), 
                    ('ebay_site','EBAY-IT', 'eBay Italy'), 
                    ('ebay_site','EBAY-NL', 'eBay Netherlands'), 
                    ('ebay_site','EBAY-ES', 'eBay Spain'), 
                    ('ebay_site','EBAY-CH', 'eBay Switzerland'), 
                    ('ebay_site','EBAY-HK', 'eBay Hong Kong'), 
                    ('ebay_site','EBAY-IN', 'eBay India'), 
                    ('ebay_site','EBAY-IE', 'eBay Ireland'), 
                    ('ebay_site','EBAY-MY', 'eBay Malaysia'), 
                    ('ebay_site','EBAY-PH', 'eBay Philippines'), 
                    ('ebay_site','EBAY-PL', 'eBay Poland'), 
                    ('ebay_site','EBAY-SG', 'eBay Singapore');";
            $wpdb->query( $sql );
        }

        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}wpcp_data` WHERE data_type='ebay_category'" ) ) {
            $sql = "INSERT INTO `{$wpdb->prefix}wpcp_data` (`data_type`, `data_id`, `data`)
                    VALUES ('ebay_category','motors-parts-accessories', 'Parts & Accessories - Motors'), 
                    ('ebay_category','vehicles', 'Vehicles - Motors'), 
                    ('ebay_category','beauty', 'Beauty - Fashion'), 
                    ('ebay_category','handbags', 'Handbags & Accessories - Fashion'), 
                    ('ebay_category','health', 'Health - Fashion'), 
                    ('ebay_category','jewelry', 'Jewelry - Fashion'), 
                    ('ebay_category','kids-baby', 'Kids & Baby (Kids Clothing, Shoes & Accs) - Fashion'), 
                    ('ebay_category','mens-clothing', 'Men Clothing - Fashion'), 
                    ('ebay_category','shoes', 'Shoes - Fashion'), 
                    ('ebay_category','watches', 'Watches - Fashion'), 
                    ('ebay_category','womens-clothing', 'Women Clothing - Fashion'), 
                    ('ebay_category','camera-photo', 'Camera & Photo - Electronics'), 
                    ('ebay_category','more-electronics', 'Car Electronics - Electronics'), 
                    ('ebay_category','cell-phone-pda', 'Cell Phones & Accessories - Electronics'), 
                    ('ebay_category','computers-networking', 'Computer & Tablets - Electronics'), 
                    ('ebay_category','tv-video-audio', 'TV, Video, & Audio - Electronics'), 
                    ('ebay_category','video-games', 'Video Games & Consoles - Electronics'), 
                    ('ebay_category','antiques', 'Antiques - Collectibles & Art'), 
                    ('ebay_category','art', 'Art - Collectibles & Art'), 
                    ('ebay_category','coins', 'Coins & Paper Money - Collectibles & Art'), 
                    ('ebay_category','collectibles', 'Collectibles - Collectibles & Art'), 
                    ('ebay_category','comics', 'Comics - Collectibles & Art'), 
                    ('ebay_category','dolls-bears', 'Dolls & Bears - Collectibles & Art'), 
                    ('ebay_category','entertainment-memorabilia', 'Entertainment Memorabilia - Collectibles & Art'), 
                    ('ebay_category','pottery-glass', 'Potery & Glass - Collectibles & Art'), 
                    ('ebay_category','sports-mem', 'Sports Memorablilia - Collectibles & Art'), 
                    ('ebay_category','stamps', 'Stamps - Collectibles & Art'), 
                    ('ebay_category','baby', 'Baby - Home & Garden'), 
                    ('ebay_category','bedding', 'Bedding - Home & Garden'), 
                    ('ebay_category','crafts', 'Crafts - Home & Garden'), 
                    ('ebay_category','furniture', 'Furniture - Home & Garden'), 
                    ('ebay_category','decor', 'Home Décor - Home & Garden'), 
                    ('ebay_category','home-improvement', 'Home Improvement - Home & Garden'), 
                    ('ebay_category','housekeeping', 'Housekeeping & Organization - Home & Garden'), 
                    ('ebay_category','kitchen-dining', 'Kitchen & Dining - Home & Garden'), 
                    ('ebay_category','major-appliances', 'Major Appliances - Home & Garden'), 
                    ('ebay_category','pet-supplies', 'Pet Supplies - Home & Garden'), 
                    ('ebay_category','tools', 'Tools - Home & Garden'), 
                    ('ebay_category','yard-garden', 'Yard & Garden - Home & Garden'), 
                    ('ebay_category','boxing-martial-arts-mma', 'Boxing, Martial Arts & MMA - Sporting Goods'), 
                    ('ebay_category','cycling', 'Cycling - Sporting Goods'), 
                    ('ebay_category','fishing', 'Fishing - Sporting Goods'), 
                    ('ebay_category','exercise-fitness', 'Fitness & Running - Sporting Goods'), 
                    ('ebay_category','golf', 'Golf - Sporting Goods'), 
                    ('ebay_category','hunting', 'Hunting - Sporting Goods'), 
                    ('ebay_category','indoor-games', 'Indoor Games - Sporting Goods'), 
                    ('ebay_category','outdoor-sports', 'Outdoor Sports - Sporting Goods'), 
                    ('ebay_category','wholesale-sport-lots', 'Sporting Goods Wholesale Lots - Sporting Goods'), 
                    ('ebay_category','team-sports', 'Team Sports - Sporting Goods'), 
                    ('ebay_category','tennis-racquet-sports', 'Tennis & Racquet Sports - Sporting Goods'), 
                    ('ebay_category','water-sports', 'Water Sports - Sporting Goods'), 
                    ('ebay_category','winter-sports', 'Winter Sports - Sporting Goods'), 
                    ('ebay_category','action-figures', 'Action Figures - Toys & Hobbies'), 
                    ('ebay_category','building-toys', 'Building Toys - Toys & Hobbies'), 
                    ('ebay_category','educational', 'Educational - Toys & Hobbies'), 
                    ('ebay_category','games', 'Games - Toys & Hobbies'), 
                    ('ebay_category','model-railroads-trains', 'Model Railroads & Trains - Toys & Hobbies'), 
                    ('ebay_category','radio-control-control-line', 'Radio Control and Control Line - Toys & Hobbies'), 
                    ('ebay_category','agriculture-forestry', 'Agriculture & Forestry - Business & Industrial'), 
                    ('ebay_category','construction', 'Construction - Business & Industrial'), 
                    ('ebay_category','electrical-test-equipment', 'Electrical & Test Equipment - Business & Industrial'), 
                    ('ebay_category','office', 'General Office - Business & Industrial'), 
                    ('ebay_category','healthcare-lab-life-science', 'Healthcare, Lab & Life Sciences - Business & Industrial'), 
                    ('ebay_category','heavy-equipment', 'Heavy Equipment - Business & Industrial'), 
                    ('ebay_category','mro-industrial-supply', 'MRO & Industrial Supply - Business & Industrial'), 
                    ('ebay_category','manufacturing-metalworking', 'Manufacturing & Metalworking - Business & Industrial'), 
                    ('ebay_category','packing-shipping', 'Packing & Shipping - Business & Industrial'), 
                    ('ebay_category','restaurant-catering', 'Restaurant & Catering - Business & Industrial'), 
                    ('ebay_category','retail-services', 'Retail & Services - Business & Industrial'), 
                    ('ebay_category','musical-instruments-gear', 'Musical Instruments and Gear - Music'), 
                    ('ebay_category','recorded-music', 'Recorded Music - Music'), 
                    ('ebay_category','tickets-experiences', 'Tickets & Experiences - Music');";
            $wpdb->query( $sql );
        }

        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}wpcp_data` WHERE data_type='youtube_category'" ) ) {
            $sql = "INSERT INTO `{$wpdb->prefix}wpcp_data` (`data_type`, `data_id`, `data`)
                    VALUES ('youtube_category','2', 'Autos & Vehicles'), 
                    ('youtube_category','1', 'Film & Animation'), 
                    ('youtube_category','10', 'Music'), 
                    ('youtube_category','15', 'Pets & Animals'), 
                    ('youtube_category','17', 'Sports'), 
                    ('youtube_category','18', 'Short Movies'), 
                    ('youtube_category','19', 'Travel & Events'), 
                    ('youtube_category','20', 'Gaming'), 
                    ('youtube_category','21', 'Videoblogging'), 
                    ('youtube_category','22', 'People & Blogs'), 
                    ('youtube_category','23', 'Comedy'), 
                    ('youtube_category','24', 'Entertainment'), 
                    ('youtube_category','25', 'News & Politics'), 
                    ('youtube_category','26', 'Howto & Style'), 
                    ('youtube_category','27', 'Education'), 
                    ('youtube_category','28', 'Science & Technology'), 
                    ('youtube_category','29', 'Nonprofits & Activism'), 
                    ('youtube_category','30', 'Movies'), 
                    ('youtube_category','31', 'Anime/Animation'), 
                    ('youtube_category','32', 'Action/Adventure'), 
                    ('youtube_category','33', 'Classics'), 
                    ('youtube_category','34', 'Comedy'), 
                    ('youtube_category','35', 'Documentary'), 
                    ('youtube_category','36', 'Drama'), 
                    ('youtube_category','37', 'Family'), 
                    ('youtube_category','38', 'Foreign'), 
                    ('youtube_category','39', 'Horror'), 
                    ('youtube_category','40', 'Sci-Fi/Fantasy'), 
                    ('youtube_category','41', 'Thriller'), 
                    ('youtube_category','42', 'Shorts'), 
                    ('youtube_category','43', 'Shows'), 
                    ('youtube_category','44', 'Trailers');";
            $wpdb->query( $sql );
        }

        if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}wpcp_data` WHERE data_type='bestbuy_category'" ) ) {
            $sql = "INSERT INTO `{$wpdb->prefix}wpcp_data` (`data_type`, `data_id`, `data`)
                    VALUES ('bestbuy_category','1', 'All Cell Phones with Plans'), 
                    ('bestbuy_category','2', 'Desktop & All-in-One Computers'), 
                    ('bestbuy_category','3', 'Digital Cameras'), 
                    ('bestbuy_category','4', 'Health, Fitness & Beauty'), 
                    ('bestbuy_category','5', 'Headphones'), 
                    ('bestbuy_category','6', 'Home Audio'), 
                    ('bestbuy_category','7', 'Home Automation & Security'), 
                    ('bestbuy_category','8', 'iPad, Tablets & E-Readers'), 
                    ('bestbuy_category','9', 'Laptops'), 
                    ('bestbuy_category','10', 'Nintendo 3DS'), 
                    ('bestbuy_category','11', 'PlayStation 4'), 
                    ('bestbuy_category','12', 'Portable & Wireless Speakers'), 
                    ('bestbuy_category','13', 'PS Vita'), 
                    ('bestbuy_category','14', 'Ranges, Cooktops & Ovens'), 
                    ('bestbuy_category','15', 'Refrigerators'), 
                    ('bestbuy_category','16', 'Small Kitchen Appliances'), 
                    ('bestbuy_category','17', 'TVs'), 
                    ('bestbuy_category','18', 'Washers & Dryers'), 
                    ('bestbuy_category','19', 'Wii U'), 
                    ('bestbuy_category','20', 'Xbox One');";
            $wpdb->query( $sql );
        }
    }

    /**
     * Create cron jobs
     *
     * @return void
     */
    public function create_cron_jobs() {
        wp_schedule_event( time(), 'once_a_minute', 'wpcp_per_minute_scheduled_events' );
        wp_schedule_event( time(), 'daily', 'wpcp_daily_scheduled_events' );

    }

    public function deactivate() {
        wp_clear_scheduled_hook( 'wpcp_per_minute_scheduled_events' );
        wp_clear_scheduled_hook( 'wpcp_daily_scheduled_events' );
    }
}
