<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once WC_ABSPATH.'includes/emails/class-wc-email.php';
require_once IWD_CONNECTOR_PATH . 'includes/api/class-iwd-con-api.php';

class IWD_Fail_Order_Email extends WC_Email {

    private $session_data;

    private $apiData;

    public function __construct() {
        add_filter( 'woocommerce_email_classes', array($this, 'add_fail_order_woocommerce_email') );
        $this->id = 'iwd_order_failed_email';
        $this->title = 'Failed Order Creation Email';
        $this->description = 'Notification emails are sent when order creation failed';
        $this->heading = 'Order creation failed';
        $this->subject = 'Order creation failed';
        $this->template_base = IWD_CONNECTOR_PATH;
        $this->template_html  =  IWD_CONNECTOR_PATH . 'templates/emails/admin-fail-order.php';
        $this->template_plain =   IWD_CONNECTOR_PATH . 'templates/emails/admin-fail-order.php';

        parent::__construct();

        $this->recipient = $this->get_option( 'recipient' );

        if ( ! $this->recipient )
            $this->recipient = get_option( 'admin_email' );
    }

    /**
     * Get the template file in the current theme.
     *
     * @param  string $template Template name.
     *
     * @return string
     */
    public function get_theme_template_file($template){
        return $template;
    }

    /**
     * @param $email_classes
     * @return mixed
     */
    public function add_fail_order_woocommerce_email($email_classes){
        $email_classes['IWD_Fail_Order_Email'] = new IWD_Fail_Order_Email();

        return $email_classes;
    }

    /**
     * @param $session_data
     * @param $apiData
     * @return void
     */
    public function trigger( $session_data, $apiData ) {

        $this->find[] = '{order_date}';
        $this->replace[] = date_i18n( woocommerce_date_format(), strtotime( $this->object->order_date ) );
        $this->session_data = $session_data;
        $this->apiData = $apiData;

        if (! $this->enabled() || ! $this->is_enabled() || ! $this->get_recipient() )
            return;

        $this->send( unserialize($session_data['customer'])['email'], $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }

    /**
     * Get the email content in HTML format.
     *
     * @return string
     */
    public function get_content_html()
    {
        return wc_get_template_html(
            '',
            array(
                'session_data' => $this->session_data,
                'apiData' => $this->apiData,
                'email_heading' => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin' => true,
                'plain_text' => false,
            ),IWD_CONNECTOR_PATH . 'templates/emails/admin-fail-order.php',IWD_CONNECTOR_PATH . 'templates/emails/admin-fail-order.php'

        );
    }

    /**
     * Get the email content in plain text format.
     *
     * @return string
     */
    public function get_content_plain() {
        return wc_get_template_html(
             '',
            array(
                'session_data' => $this->session_data,
                'email_heading' => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin' => true,
                'plain_text' => false,
            ),IWD_CONNECTOR_PATH . 'templates/emails/admin-fail-order.php',IWD_CONNECTOR_PATH . 'templates/emails/admin-fail-order.php'

        );
    }

    /**
     * @return false|mixed|null
     */
    public function enabled(){
        return get_option( 'iwd_notification_enabled' );
    }

    /**
     * Initialise Settings Form Fields - these are generic email options most will use.
     */
    public function init_form_fields() {

        $this->form_fields = array(
            'enabled'    => array(
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable this email notification',
                'default' => 'yes'
            ),
            'recipient'  => array(
                'title'       => 'Recipient(s)',
                'type'        => 'text',
                'description' => sprintf( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', esc_attr( get_option( 'admin_email' ) ) ),
                'placeholder' => '',
                'default'     => ''
            ),
            'subject'    => array(
                'title'       => 'Subject',
                'type'        => 'text',
                'description' => sprintf( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
                'placeholder' => '',
                'default'     => ''
            ),
            'heading'    => array(
                'title'       => 'Email Heading',
                'type'        => 'text',
                'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.' ), $this->heading ),
                'placeholder' => '',
                'default'     => ''
            ),
            'email_type' => array(
                'title'       => 'Email type',
                'type'        => 'select',
                'description' => 'Choose which format of email to send.',
                'default'     => 'html',
                'class'       => 'email_type',
                'options'     => array(
                    'plain'     => 'Plain text',
                    'html'      => 'HTML', 'woocommerce',
                    'multipart' => 'Multipart', 'woocommerce',
                )
            )
        );
    }
}