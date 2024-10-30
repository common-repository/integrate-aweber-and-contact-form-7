<?php

/**
 * Shows admin interface.
 *
 * @link       https://darpankulkarni.in
 * @since      1.0.0
 *
 * @package    Awb_Cf7
 * @subpackage Awb_Cf7/admin
 * @author     Darpan Kulkarni <plugins@darpankulkarni.in>
 */
class Awb_Cf7_Panel {

	/**
	 * Create new Contact Form 7 panel
	 *
	 * @param $panels
	 *
	 * @return array $panels Contact Form 7 panels
	 *
	 * @since 1.0.0
	 */
	public function awbc_panel_init( $panels ) {
		$cf7_awb_panel = array(
			'awbc-panel' => array(
				'title'    => __( 'AWeber', 'contact-form-7' ),
				'callback' => array( $this, 'awbc_panel_content' )
			)
		);

		return array_merge( $panels, $cf7_awb_panel );
	}

	/**
	 * Show Contact Form 7 panel content
	 *
	 * @param WPCF7_ContactForm $cf7 Contact Form 7 instance
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function awbc_panel_content( WPCF7_ContactForm $cf7 ) {
		// Get current form id
		$formId = $cf7->id();

		// Show alert if form is not saved yet
		if ( ! $formId ) {
			?>
            <div class="awbc-alert awbc-error">
				<?php echo __( 'This plugin requires to know form id in advance. Please save the form to generate form id.' ); ?>
            </div>
			<?php
			return;
		}

		// Get all options
		$options = get_option( 'awbc_' . $formId );

		//update_option('awbc_'.$formId, array_merge( $options, array( 'created_at' => '2020-02-17 09:03:58' ) ));

		$authorized     = $options && isset( $options['access_token'] ); // Check if user is authorized
		$selectedListId = ! ( $options && isset( $options['list_id'] ) ) ?: $options['list_id']; // Check if list is connected
		$subscriber     = ! ( $options && isset( $options['subscriber'] ) ) ?: $options['subscriber']; // Check if subscriber data is available

		// Instanciate auth api class
		$auth = new Awb_Cf7_Auth();

		// Instanciate mail tags class
		$mailTags = new Awb_Cf7_Mail_Tags( $cf7->scan_form_tags() )
		?>
        <h2 class="awbc-title"><?php echo __( 'AWeber Integration' ); ?></h2>

        <input type="hidden" id="awbc_form_id" value="<?php echo esc_attr( $formId ); ?>">
        <input type="hidden" id="awbc_code_verifier" value="<?php echo esc_attr( $auth->codeVerifier ); ?>">

        <div class="awbc-alert awbc-success" style="display: none;"></div>
        <div class="awbc-alert awbc-error" style="display: none;"></div>

        <div class="awbc-alert awbc-authorized" style="display: <?php echo esc_attr( $authorized ) ? 'block' : 'none'; ?>">
			<?php echo __( 'Authorized and connected to AWeber account.' ); ?>
            <a id="awbc_revoke_auth" href="#"><?php echo __( 'Revoke authorization' ); ?></a> <?php echo __( 'to connect to another account.' ); ?>
        </div>

        <div id="awbc_auth_box" class="awbc-box" style="display: <?php echo esc_attr( $authorized ) ? 'none' : 'block'; ?>">
            <div class="awbc-title-row">
                <h3 class="awbc-sub-title"><?php echo __( 'Authorize' ); ?></h3>
                <a class="button" href="<?php echo esc_attr( $auth->awbc_get_auth_code_url() ); ?>" target="_blank">
					<?php echo __( 'Generate Authorization Code' ); ?>
                </a>
            </div>

            <div class="awbc-row">
                <div class="awbc-col">
                    <label for="awbc_auth_code"><?php echo __( 'Authorization Code' ); ?>:<span class="awbc-required">*</span></label>
                    <input type="text" id="awbc_auth_code" placeholder="<?php echo __( 'Generate and paste your authorization code here' ); ?>">
                    <button id="awbc_get_auth_code" class="button-primary"><?php echo __( 'Authorize and Fetch Lists' ); ?></button>
                </div>
            </div>
        </div>

        <div id="awbc_list_box" class="awbc-box" style="display: <?php echo esc_attr( $authorized ) ? 'block' : 'none'; ?>">
            <div class="awbc-title-row">
                <h3 class="awbc-sub-title"><?php echo __( 'Select List' ); ?></h3>
                <button id="awbc_reload_lists" class="button"><?php echo __( 'Reload Lists' ); ?></button>
            </div>

            <div class="awbc-row">
                <div class="awbc-col">
                    <label for="awbc_lists"><?php echo __( 'AWeber Lists' ); ?>:<span class="awbc-required">*</span></label>
                    <select id="awbc_lists">
						<?php
						if ( $options && isset( $options['lists'] ) ) {
							foreach ( $options['lists'] as $key => $value ) {
								?>
                                <option value="<?php echo esc_attr( $key ); ?>"
									<?php echo esc_attr( $selectedListId ) == esc_attr( $key ) ? 'selected' : ''; ?>>
									<?php echo '[' . esc_attr( $key ) . '] ' . esc_attr( $value ); ?>
                                </option>
								<?php
							}
						}
						?>
                    </select>
                    <button id="awbc_connect_list" class="button-primary"><?php echo __( 'Connect to Selected List' ); ?></button>
                </div>
            </div>
        </div>

        <div id="awbc_sub_box" class="awbc-box" style="display: <?php echo ( $options && isset( $options['list_id'] ) ) ? 'block' : 'none'; ?>">
            <div class="awbc-title-row">
                <h3 class="awbc-sub-title"><?php echo __( 'Subscriber Details' ); ?></h3>
            </div>

            <div class="awbc-row">
                <div class="awbc-col">
					<?php $mailTags->render_select_box( 'Subscriber Email', 'awbc_sub_email', $subscriber['email'], true ); ?>
                </div>

                <div class="awbc-col">
					<?php $mailTags->render_select_box( 'Subscriber Name', 'awbc_sub_name', $subscriber['name'] ); ?>
                </div>
            </div>
        </div>
		<?php
	}
}

class Awb_Cf7_Mail_Tags {
	private $mailTags;

	public function __construct( $mailTags ) {
		$this->mailTags = $mailTags;
	}

	public function render_select_box( $label, $name, $value, $required = false ) {
		?>
        <label for="<?php echo esc_attr( $name ); ?>">
			<?php echo esc_attr__( $label ); ?>:<?php echo $required ? '<span class="awbc-required">*</span>' : ''; ?>
        </label>
        <select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>">
			<?php
			if ( ! $required ) {
				echo '<option value="">Leave empty</option>';
			}
			foreach ( $this->mailTags as $tag ) {
				if ( $tag->name != '' ) {
					$tagName = '[' . esc_attr( $tag->name ) . ']';
					?>
                    <option value="<?php echo $tagName; ?>" <?php echo $tagName == esc_attr( $value ) ? 'selected' : ''; ?>>
						<?php echo $tagName; ?>
                    </option>
					<?php
				}
			}
			?>
        </select>
		<?php
	}
}
