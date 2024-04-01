<?php

/*

Plugin Name: AB Zoho Bridge

Plugin URI:

Description: Connect your site to ZOHO CRM

Version: 2.0.0

Author: Fawaz

*/



// Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) exit;



class Zoho_Bridge{



    /**

	 * The Zoho Account instance variable.

	 *

	 * @access public

	 * @since  2.0

	 * @var    Zoho_Account

	 */



    public $account;



    /**

	 * The Zoho Contact instance variable.

	 *

	 * @access public

	 * @since  2.0

	 * @var    Zoho_Contact

	 */



    public $contact;



    /**

	 * The Zoho User instance variable.

	 *

	 * @access public

	 * @since  2.0

	 * @var    Zoho_User

	 */



    public $user;



    /**

	 * The Zoho Curl instance variable.

	 *

	 * @access public

	 * @since  2.0

	 * @var    Zoho_Curl

	 */



    public $curl;



    /**

	 * The Zoho Log instance variable.

	 *

	 * @access public

	 * @since  2.0

	 * @var    Zoho_Log

	 */



    public $log;



    /**

	 * The Zoho Leads instance variable.

	 *

	 * @access public

	 * @since  2.0

	 * @var    Zoho_Leads

	 */



    public $leads;



    /**

	 * The Zoho Sales Order instance variable.

	 *

	 * @access public

	 * @since  2.0

	 * @var    ZohoSalesOrder

	 */



    public $sales_order;



    /**

	 * The Zoho Territory instance variable.

	 *

	 * @access public

	 * @since  2.0

	 * @var    Territory

	 */



    public $territory;



    /**

	 * The Zoho Project instance variable.

	 *

	 * @access public

	 * @since  2.0

	 * @var    project

	 */



    public $project;



    /**

     * Class Constructer

     * 

     */



    public function __construct(){



        // Define Plugin Variables

        $this->defines();



        // Include Plugin Extra Files

        $this->includes();



        // Create Objects

        $this->objects();

    }



    /**

     * Define Plugin Variable

     * 

     * @since   2.0.0

     */

    

    function defines(){



        @define( 'ZB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

        @define( 'ZB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		@define( 'ZB_DOMAIN', "com" );

    }



    /**

     * Include Plugin Files

     * 

     * @since   2.0.0

     */



    function includes(){



        require_once ZB_PLUGIN_DIR.'admin/dashboard.php';



        require_once ZB_PLUGIN_DIR.'classes/class.log.php';

        require_once ZB_PLUGIN_DIR.'classes/class.curl.php';

        require_once ZB_PLUGIN_DIR.'classes/class.leads.php';



        require_once ZB_PLUGIN_DIR.'inc/core-functions.php';

        require_once ZB_PLUGIN_DIR.'inc/actions.php';

    }



    /**

	 * Create Class Objects.

	 *

	 * @access public

	 * @since  2.0

	 */



    function objects(){



        $this->curl     = new Zoho_Curl;

        $this->log      = new Zoho_Log;

        $this->leads    = new Zoho_Leads;

    }

}



new Zoho_Bridge();

?>