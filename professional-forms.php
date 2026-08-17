<?php
/**
 * Plugin Name: Professional Contact Forms
 * Plugin URI:  https://github.com/Byot3711/-Forms
 * Description: Plugin profesional pentru formulare de contact în WordPress — validare securizată prin nonce, stocare a trimiterilor în baza de date, notificări automate pe email și panou de administrare dedicat pentru gestionarea mesajelor primite.
 * Version:     1.0.0
 * Author:      Byot
 * Author URI:  https://github.com/Byot3711
 * License:     GPL-2.0+
 * Text Domain: professional-forms
 */

// Dacă acest fișier este apelat direct, ieși.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'PROFESSIONAL_FORMS_VERSION', '1.0.0' );

/**
 * Clasa principală a plugin-ului.
 */
class Professional_Forms {

    /**
     * Constructor.
     */
    public function __construct() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_uninstall_hook( __FILE__, array( 'Professional_Forms', 'uninstall' ) );

        add_shortcode( 'professional_form', array( $this, 'form_shortcode' ) );

        add_action( 'admin_post_nopriv_professional_form_submit', array( $this, 'handle_submission' ) );
        add_action( 'admin_post_professional_form_submit', array( $this, 'handle_submission' ) );

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Activare plugin: creează tabela și opțiunea implicită.
     */
    public function activate() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'professional_forms_submissions';
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

        // Setează email-ul destinatarului ca email-ul adminului, dacă nu există deja.
        if ( ! get_option( 'professional_forms_email' ) ) {
            update_option( 'professional_forms_email', get_option( 'admin_email' ) );
        }
    }

    /**
     * Dezinstalare: șterge tabela și opțiunea.
     */
    public static function uninstall() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'professional_forms_submissions';
        $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
        delete_option( 'professional_forms_email' );
    }

    /**
     * Înregistrează stilurile front-end.
     */
    public function enqueue_scripts() {
        // CSS inline pentru a păstra plugin-ul într-un singur fișier.
        $custom_css = "
            .professional-form-wrapper {
                max-width: 600px;
                margin: 20px auto;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            .professional-form-wrapper form {
                background: #f9f9f9;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            }
            .professional-form-wrapper .form-group {
                margin-bottom: 20px;
            }
            .professional-form-wrapper label {
                display: block;
                margin-bottom: 5px;
                font-weight: 600;
                color: #333;
            }
            .professional-form-wrapper input[type='text'],
            .professional-form-wrapper input[type='email'],
            .professional-form-wrapper textarea {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 16px;
                box-sizing: border-box;
            }
            .professional-form-wrapper textarea {
                height: 150px;
                resize: vertical;
            }
            .professional-form-wrapper button[type='submit'] {
                background: #0073aa;
                color: white;
                border: none;
                padding: 12px 25px;
                font-size: 16px;
                border-radius: 4px;
                cursor: pointer;
                transition: background 0.3s;
            }
            .professional-form-wrapper button[type='submit']:hover {
                background: #005a87;
            }
            .professional-form-message {
                margin-top: 20px;
                padding: 15px;
                border-radius: 4px;
            }
            .professional-form-success {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .professional-form-error {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
        ";
        wp_register_style( 'professional-forms-style', false );
        wp_enqueue_style( 'professional-forms-style' );
        wp_add_inline_style( 'professional-forms-style', $custom_css );
    }

    /**
     * Shortcode [professional_form] – afișează formularul.
     */
    public function form_shortcode() {
        ob_start();
        ?>
        <div class="professional-form-wrapper">
            <?php
            // Afișează mesajele de succes/eroare stocate în sesiune.
            if ( isset( $_GET['pf_status'] ) ) {
                $status = sanitize_text_field( $_GET['pf_status'] );
                $message = '';
                if ( $status === 'success' ) {
                    $message = __( 'Mesajul tău a fost trimis cu succes!', 'professional-forms' );
                    echo '<div class="professional-form-message professional-form-success">' . esc_html( $message ) . '</div>';
                } elseif ( $status === 'error' ) {
                    $message = __( 'A apărut o eroare. Te rugăm să încerci din nou.', 'professional-forms' );
                    echo '<div class="professional-form-message professional-form-error">' . esc_html( $message ) . '</div>';
                }
            }
            ?>
            <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
                <?php wp_nonce_field( 'professional_form_action', 'professional_form_nonce' ); ?>
                <input type="hidden" name="action" value="professional_form_submit">
                <div class="form-group">
                    <label for="pf-name"><?php _e( 'Nume', 'professional-forms' ); ?></label>
                    <input type="text" id="pf-name" name="pf_name" required>
                </div>
                <div class="form-group">
                    <label for="pf-email"><?php _e( 'Email', 'professional-forms' ); ?></label>
                    <input type="email" id="pf-email" name="pf_email" required>
                </div>
                <div class="form-group">
                    <label for="pf-message"><?php _e( 'Mesaj', 'professional-forms' ); ?></label>
                    <textarea id="pf-message" name="pf_message" required></textarea>
                </div>
                <button type="submit"><?php _e( 'Trimite', 'professional-forms' ); ?></button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Procesează trimiterea formularului.
     */
    public function handle_submission() {
        // Verifică nonce-ul.
        if ( ! isset( $_POST['professional_form_nonce'] ) || ! wp_verify_nonce( $_POST['professional_form_nonce'], 'professional_form_action' ) ) {
            wp_die( __( 'Verificarea de securitate a eșuat.', 'professional-forms' ) );
        }

        // Preia și sanitizează datele.
        $name    = isset( $_POST['pf_name'] ) ? sanitize_text_field( $_POST['pf_name'] ) : '';
        $email   = isset( $_POST['pf_email'] ) ? sanitize_email( $_POST['pf_email'] ) : '';
        $message = isset( $_POST['pf_message'] ) ? sanitize_textarea_field( $_POST['pf_message'] ) : '';

        // Validare simplă.
        if ( empty( $name ) || empty( $email ) || empty( $message ) || ! is_email( $email ) ) {
            $redirect = add_query_arg( 'pf_status', 'error', wp_get_referer() );
            wp_safe_redirect( $redirect );
            exit;
        }

        // Salvează în baza de date.
        global $wpdb;
        $table_name = $wpdb->prefix . 'professional_forms_submissions';
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
            $redirect = add_query_arg( 'pf_status', 'error', wp_get_referer() );
            wp_safe_redirect( $redirect );
            exit;
        }

        // Trimite notificare pe email.
        $to = get_option( 'professional_forms_email', get_option( 'admin_email' ) );
        $subject = sprintf( __( 'Mesaj nou de la %s', 'professional-forms' ), $name );
        $body = sprintf(
            __( "Nume: %s\nEmail: %s\nMesaj:\n%s", 'professional-forms' ),
            $name,
            $email,
            $message
        );
        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        wp_mail( $to, $subject, $body, $headers );

        // Redirecționează cu succes.
        $redirect = add_query_arg( 'pf_status', 'success', wp_get_referer() );
        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * Adaugă paginile de administrare.
     */
    public function admin_menu() {
        add_menu_page(
            __( 'Formulare', 'professional-forms' ),
            __( 'Formulare', 'professional-forms' ),
            'manage_options',
            'professional-forms',
            array( $this, 'submissions_page' ),
            'dashicons-email-alt',
            30
        );
        add_submenu_page(
            'professional-forms',
            __( 'Trimiteri', 'professional-forms' ),
            __( 'Trimiteri', 'professional-forms' ),
            'manage_options',
            'professional-forms',
            array( $this, 'submissions_page' )
        );
        add_submenu_page(
            'professional-forms',
            __( 'Setări', 'professional-forms' ),
            __( 'Setări', 'professional-forms' ),
            'manage_options',
            'professional-forms-settings',
            array( $this, 'settings_page' )
        );
    }

    /**
     * Pagina cu lista trimiterilor.
     */
    public function submissions_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'professional_forms_submissions';

        // Paginare simplă.
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
            <h1><?php _e( 'Trimiteri formulare', 'professional-forms' ); ?></h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e( 'ID', 'professional-forms' ); ?></th>
                        <th><?php _e( 'Nume', 'professional-forms' ); ?></th>
                        <th><?php _e( 'Email', 'professional-forms' ); ?></th>
                        <th><?php _e( 'Mesaj', 'professional-forms' ); ?></th>
                        <th><?php _e( 'Data', 'professional-forms' ); ?></th>
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
                            <td colspan="5"><?php _e( 'Nu există trimiteri.', 'professional-forms' ); ?></td>
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
     * Pagina de setări.
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'Setări Professional Forms', 'professional-forms' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'professional_forms_settings' );
                do_settings_sections( 'professional_forms_settings' );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="professional_forms_email"><?php _e( 'Email destinatar', 'professional-forms' ); ?></label>
                        </th>
                        <td>
                            <input type="email" id="professional_forms_email" name="professional_forms_email"
                                   value="<?php echo esc_attr( get_option( 'professional_forms_email', get_option( 'admin_email' ) ) ); ?>"
                                   class="regular-text">
                            <p class="description"><?php _e( 'Adresa de email la care se trimit notificările pentru formularele completate.', 'professional-forms' ); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Înregistrează setarea pentru email.
     */
    public function register_settings() {
        register_setting( 'professional_forms_settings', 'professional_forms_email', 'sanitize_email' );
    }
}

// Inițializează plugin-ul.
new Professional_Forms();
