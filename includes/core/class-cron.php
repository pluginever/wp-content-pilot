<?php
namespace Pluginever\WPCP\Core;

class Cron{

    public function __construct() {
        add_filter( 'cron_schedules', [ $this, 'custom_cron_schedules' ] );
    }

    /**
     * Add custom cron schedule
     *
     * @param $schedules
     *
     * @return mixed
     */
    public function custom_cron_schedules( $schedules ) {
        $schedules ['once_a_minute'] = array(
            'interval' => 60,
            'display'  => __( 'Once a Minute' )
        );

        return $schedules;
    }
}
