<?php
/**
 * Plugin Name: FormLite
 * Plugin URI:  https://github.com/Byot3711/-Forms
 * Description: A lightweight WordPress contact form plugin with database storage, email notifications, and an admin dashboard.
 * Version:     1.0.0
 * Author:      Byot
 * Author URI:  https://github.com/Byot3711
 * License:     GPL-2.0+
 * Text Domain: formlite
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'FORMLITE_VERSION', '1.0.0' );

/**
 * Main plugin class.
 */
class FormLite {

    /**
     * Constructor.
     */
    public function __construct() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_uninstall_hook( __FILE__, array( 'FormLite', 'uninstall' ) );

        add_shortcode( 'formlite', array( $this, 'form_shortcode' ) );

        add_action( 'admin_post_nopriv_formlite_submit', array( $this, 'handle_submission' ) );
        add_action( 'admin_post_formlite_submit', array( $this, 'handle_submission' ) );

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Plugin activation: creates the database table and default option.
     */
    public function activate() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'formlite_submissions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            email varchar(100) NOT NULL,
            message text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        // Default the recipient email to the site admin email, if not already set.
        if ( ! get_option( 'formlite_email' ) ) {
            update_option( 'formlite_email', get_option( 'admin_email' ) );
        }
    }

    /**
     * Plugin uninstallation: drops the table and removes the option.
     */
    public static function uninstall() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'formlite_submissions';
        $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
        delete_option( 'formlite_email' );
    }

    /**
     * Registers front-end styles.
     */
    public function enqueue_scripts() {
        // Inline CSS to keep the plugin self-contained in a single file.
        $custom_css = "
            .formlite-wrapper {
                max-width: 600px;
                margin: 20px auto;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            .formlite-wrapper form {
                background: #f9f9f9;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            }
            .formlite-wrapper .form-group {
                margin-bottom: 20px;
            }
            .formlite-wrapper label {
                display: block;
                margin-bottom: 5px;
                font-weight: 600;
                color: #333;
            }
            .formlite-wrapper input[type='text'],
            .formlite-wrapper input[type='email'],
            .formlite-wrapper textarea {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 16px;
                box-sizing: border-box;
            }
            .formlite-wrapper textarea {
                height: 150px;
                resize: vertical;
            }
            .formlite-wrapper button[type='submit'] {
                background: #0073aa;
                color: white;
                border: none;
                padding: 12px 25px;
                font-size: 16px;
                border-radius: 4px;
                cursor: pointer;
                transition: background 0.3s;
            }
            .formlite-wrapper button[type='submit']:hover {
                background: #005a87;
            }
            .formlite-message {
                margin-top: 20px;
                padding: 15px;
                border-radius: 4px;
            }
            .formlite-success {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .formlite-error {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
        ";
        wp_register_style( 'formlite-style', false );
        wp_enqueue_style( 'formlite-style' );
        wp_add_inline_style( 'formlite-style', $custom_css );
    }

    /**
     * Shortcode [formlite] – renders the contact form.
     */
    public function form_shortcode() {
        ob_start();
        ?>
        <div class="formlite-wrapper">
            <?php
            // Show success/error messages after submission.
            if ( isset( $_GET['formlite_status'] ) ) {
                $status = sanitize_text_field( $_GET['formlite_status'] );
                $message = '';
                if ( $status === 'success' ) {
                    $message = __( 'Your message has been sent successfully!', 'formlite' );
                    echo '<div class="formlite-message formlite-success">' . esc_html( $message ) . '</div>';
                } elseif ( $status === 'error' ) {
                    $message = __( 'Something went wrong. Please try again.', 'formlite' );
                    echo '<div class="formlite-message formlite-error">' . esc_html( $message ) . '</div>';
                }
            }
            ?>
            <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
                <?php wp_nonce_field( 'formlite_action', 'formlite_nonce' ); ?>
                <input type="hidden" name="action" value="formlite_submit">
                <div class="form-group">
                    <label for="formlite-name"><?php _e( 'Name', 'formlite' ); ?></label>
                    <input type="text" id="formlite-name" name="formlite_name" required>
                </div>
                <div class="form-group">
                    <label for="formlite-email"><?php _e( 'Email', 'formlite' ); ?></label>
                    <input type="email" id="formlite-email" name="formlite_email" required>
                </div>
                <div class="form-group">
                    <label for="formlite-message"><?php _e( 'Message', 'formlite' ); ?></label>
                    <textarea id="formlite-message" name="formlite_message" required></textarea>
                </div>
                <button type="submit"><?php _e( 'Send', 'formlite' ); ?></button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Processes the form submission.
     */
    public function handle_submission() {
        // Verify the nonce.
        if ( ! isset( $_POST['formlite_nonce'] ) || ! wp_verify_nonce( $_POST['formlite_nonce'], 'formlite_action' ) ) {
            wp_die( __( 'Security check failed.', 'formlite' ) );
        }

        // Retrieve and sanitize the submitted data.
        $name    = isset( $_POST['formlite_name'] ) ? sanitize_text_field( $_POST['formlite_name'] ) : '';
        $email   = isset( $_POST['formlite_email'] ) ? sanitize_email( $_POST['formlite_email'] ) : '';
        $message = isset( $_POST['formlite_message'] ) ? sanitize_textarea_field( $_POST['formlite_message'] ) : '';

        // Basic validation.
        if ( empty( $name ) || empty( $email ) || empty( $message ) || ! is_email( $email ) ) {
            $redirect = add_query_arg( 'formlite_status', 'error', wp_get_referer() );
            wp_safe_redirect( $redirect );
            exit;
        }

        // Save to the database.
        global $wpdb;
        $table_name = $wpdb->prefix . 'formlite_submissions';
        $inserted = $wpdb->insert(
            $table_name,
            array(
                'name'    => $name,
                'email'   => $email,
                'message' => $message,
            ),
            array( '%s', '%s', '%s' )
        );

        if ( false === $inserted ) {
            $redirect = add_query_arg( 'formlite_status', 'error', wp_get_referer() );
            wp_safe_redirect( $redirect );
            exit;
        }

        // Send the email notification.
        $to = get_option( 'formlite_email', get_option( 'admin_email' ) );
        $subject = sprintf( __( 'New message from %s', 'formlite' ), $name );
        $body = sprintf(
            __( "Name: %s\nEmail: %s\nMessage:\n%s", 'formlite' ),
            $name,
            $email,
            $message
        );
        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        wp_mail( $to, $subject, $body, $headers );

        // Redirect with success status.
        $redirect = add_query_arg( 'formlite_status', 'success', wp_get_referer() );
        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * Adds the admin pages.
     */
    public function admin_menu() {
        add_menu_page(
            __( 'FormLite', 'formlite' ),
            __( 'FormLite', 'formlite' ),
            'manage_options',
            'formlite',
            array( $this, 'submissions_page' ),
            'dashicons-email-alt',
            30
        );
        add_submenu_page(
            'formlite',
            __( 'Submissions', 'formlite' ),
            __( 'Submissions', 'formlite' ),
            'manage_options',
            'formlite',
            array( $this, 'submissions_page' )
        );
        add_submenu_page(
            'formlite',
            __( 'Instructions', 'formlite' ),
            __( 'Instructions', 'formlite' ),
            'manage_options',
            'formlite-instructions',
            array( $this, 'instructions_page' )
        );
        add_submenu_page(
            'formlite',
            __( 'Settings', 'formlite' ),
            __( 'Settings', 'formlite' ),
            'manage_options',
            'formlite-settings',
            array( $this, 'settings_page' )
        );
    }

    /**
     * Submissions list page.
     */
    public function submissions_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'formlite_submissions';

        // Simple pagination.
        $per_page = 20;
        $current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $offset = ( $current_page - 1 ) * $per_page;

        $total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );
        $total_pages = ceil( $total_items / $per_page );

        $submissions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );
        ?>
        <div class="wrap">
            <h1><?php _e( 'Form Submissions', 'formlite' ); ?></h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e( 'ID', 'formlite' ); ?></th>
                        <th><?php _e( 'Name', 'formlite' ); ?></th>
                        <th><?php _e( 'Email', 'formlite' ); ?></th>
                        <th><?php _e( 'Message', 'formlite' ); ?></th>
                        <th><?php _e( 'Date', 'formlite' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( $submissions ) : ?>
                        <?php foreach ( $submissions as $sub ) : ?>
                            <tr>
                                <td><?php echo esc_html( $sub->id ); ?></td>
                                <td><?php echo esc_html( $sub->name ); ?></td>
                                <td><?php echo esc_html( $sub->email ); ?></td>
                                <td><?php echo esc_html( $sub->message ); ?></td>
                                <td><?php echo esc_html( $sub->created_at ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5"><?php _e( 'No submissions yet.', 'formlite' ); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ( $total_pages > 1 ) : ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links( array(
                            'base'      => add_query_arg( 'paged', '%#%' ),
                            'format'    => '',
                            'prev_text' => __( '&laquo;' ),
                            'next_text' => __( '&raquo;' ),
                            'total'     => $total_pages,
                            'current'   => $current_page,
                        ) );
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Instructions page – explains how to place the form on a page.
     */
    public function instructions_page() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'How to Add the Form to a Page', 'formlite' ); ?></h1>

            <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                <h2><?php _e( 'Step-by-step guide', 'formlite' ); ?></h2>
                <ol style="font-size: 14px; line-height: 1.8;">
                    <li><?php _e( 'Go to <strong>Pages &rarr; Add New</strong> (or open an existing page you want to edit).', 'formlite' ); ?></li>
                    <li><?php _e( 'Give the page a title, e.g. <strong>Contact</strong>.', 'formlite' ); ?></li>
                    <li><?php _e( 'In the content area, add a <strong>Shortcode</strong> block (or simply type the shortcode into a paragraph if you use the Classic Editor).', 'formlite' ); ?></li>
                    <li><?php _e( 'Paste the following shortcode into the block:', 'formlite' ); ?>
                        <div style="margin: 10px 0;">
                            <code style="background: #f0f0f1; padding: 8px 14px; border-radius: 4px; font-size: 15px; display: inline-block;">[formlite]</code>
                        </div>
                    </li>
                    <li><?php _e( 'Click <strong>Publish</strong> (or <strong>Update</strong>).', 'formlite' ); ?></li>
                    <li><?php _e( 'Open the page on your website — the contact form will appear there, fully styled and ready to use.', 'formlite' ); ?></li>
                </ol>

                <h2><?php _e( 'What happens after a visitor submits the form?', 'formlite' ); ?></h2>
                <ul style="font-size: 14px; line-height: 1.8;">
                    <li><?php _e( 'The submission is saved and appears under <strong>FormLite &rarr; Submissions</strong>.', 'formlite' ); ?></li>
                    <li><?php _e( 'An email notification is sent to the address configured under <strong>FormLite &rarr; Settings</strong>.', 'formlite' ); ?></li>
                </ul>

                <h2><?php _e( 'Tips', 'formlite' ); ?></h2>
                <ul style="font-size: 14px; line-height: 1.8;">
                    <li><?php _e( 'You can add the shortcode to as many pages as you like — for example, a footer widget or a sidebar text widget also supports shortcodes.', 'formlite' ); ?></li>
                    <li><?php _e( 'Set the notification email under <strong>FormLite &rarr; Settings</strong> if you want submissions sent somewhere other than the site admin email.', 'formlite' ); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Settings page.
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'FormLite Settings', 'formlite' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'formlite_settings' );
                do_settings_sections( 'formlite_settings' );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="formlite_email"><?php _e( 'Recipient email', 'formlite' ); ?></label>
                        </th>
                        <td>
                            <input type="email" id="formlite_email" name="formlite_email"
                                   value="<?php echo esc_attr( get_option( 'formlite_email', get_option( 'admin_email' ) ) ); ?>"
                                   class="regular-text">
                            <p class="description"><?php _e( 'The email address that receives notifications for new form submissions.', 'formlite' ); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Registers the email setting.
     */
    public function register_settings() {
        register_setting( 'formlite_settings', 'formlite_email', 'sanitize_email' );
    }
}

// Initialize the plugin.
new FormLite();
