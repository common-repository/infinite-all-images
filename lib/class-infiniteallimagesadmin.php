<?php
/**
 * Infinite All Images
 *
 * @package    InfiniteAllImages
 * @subpackage InfiniteAllImages Management screen
/*
	Copyright (c) 2016- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$infiniteallimagesadmin = new InfiniteAllImagesAdmin();

/** ==================================================
 * Management screen
 */
class InfiniteAllImagesAdmin {

	/** ==================================================
	 * Construct
	 *
	 * @since 2.07
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
		add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 10, 2 );
		add_filter( 'manage_media_columns', array( $this, 'posts_columns_attachment_id' ), 1 );
		add_action( 'manage_media_custom_column', array( $this, 'posts_custom_columns_attachment_id' ), 1, 2 );
	}

	/** ==================================================
	 * Add a "Settings" link to the plugins page
	 *
	 * @param  array  $links  links array.
	 * @param  string $file   file.
	 * @return array  $links  links array.
	 * @since 1.00
	 */
	public function settings_link( $links, $file ) {
		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = 'infinite-all-images/infiniteallimages.php';
		}
		if ( $file == $this_plugin ) {
			$links[] = '<a href="' . admin_url( 'options-general.php?page=InfiniteAllImages' ) . '">' . __( 'Settings' ) . '</a>';
		}
			return $links;
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function plugin_menu() {
		add_options_page( 'Infinite All Images Options', 'Infinite All Images', 'upload_files', 'InfiniteAllImages', array( $this, 'plugin_options' ) );
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function plugin_options() {

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
		}

		$this->options_updated();

		$scriptname = admin_url( 'options-general.php?page=InfiniteAllImages' );

		$infiniteallimages_option = get_user_option( 'infiniteallimages', get_current_user_id() );

		?>

		<div class="wrap">
		<h2>Infinite All Images</h2>

		<details>
		<summary><strong><?php esc_html_e( 'Various links of this plugin', 'infinite-all-images' ); ?></strong></summary>
		<?php $this->credit(); ?>
		</details>

		<details style="margin-bottom: 5px;">
		<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php esc_html_e( 'Shortcode', 'infinite-all-images' ); ?></strong></summary>
			<div style="padding:10px;"><?php esc_html_e( 'Please add new Page. Please write a shortcode.', 'infinite-all-images' ); ?></div>
			<div style="padding: 5px 20px; font-weight: bold;"><?php esc_html_e( 'Example', 'infinite-all-images' ); ?></div>
			<div style="padding: 5px 35px;"><code>[iai]</code></div>
			<div style="padding: 5px 35px;"><code>[iai img_size="medium" display=25 width=150 margin=2 sort="old" parent=1 loading_image="http://test.testdomain/wp-content/uploads/loader.gif" exclude_id="123, 213, 312" term_filter="category1"]</code></div>

			<div style="padding: 5px 20px; font-weight: bold;"><?php esc_html_e( 'Description of each attribute', 'infinite-all-images' ); ?></div>

			<div style="padding: 5px 35px;"><?php esc_html_e( 'Image&acute;s Size:', 'infinite-all-images' ); ?><code>img_size</code>(full, thumbnail, medium, large)</div>
			<div style="padding: 5px 35px;"><?php esc_html_e( 'Number of items per page:' ); ?><code>display</code></div>
			<div style="padding: 5px 35px;"><?php esc_html_e( 'Width of one column of the image:', 'infinite-all-images' ); ?><code>width</code>(px)</div>
			<div style="padding: 5px 35px;"><?php esc_html_e( 'Margin between images:', 'infinite-all-images' ); ?><code>margin</code>(px)</div>
			<div style="padding: 5px 35px;"><?php esc_html_e( 'Type of Sort:', 'infinite-all-images' ); ?><code>sort</code></div>
			<div style="padding: 5px 35px;"><?php esc_html_e( 'If the image is attached to the post, to link to the post URL:', 'infinite-all-images' ); ?><code>parent=1</code></div>
			<div style="padding: 5px 35px;"><?php esc_html_e( 'loading_image:', 'infinite-all-images' ); ?><code>loading_image</code>(url)</div>

			<div style="padding: 5px 35px;"><?php esc_html_e( 'Specifies a comma-separated list to exclusion media ID:', 'infinite-all-images' ); ?><code>exclude_id</code></div>

			<div style="padding: 5px 35px;"><?php esc_html_e( 'Specifies the slug of the term to be filtered by term:', 'infinite-all-images' ); ?><code>term_filter</code></div>

			<div style="padding: 5px 20px; font-weight: bold;">
			<?php esc_html_e( 'Attribute value of shortcodes can also be specified in the "Settings". Attribute value of the shortcode takes precedence.', 'infinite-all-images' ); ?>
			</div>
		</details>

		<details style="margin-bottom: 5px;">
		<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php esc_html_e( 'Filter', 'infinite-all-images' ); ?></strong></summary>
			<div style="padding: 5px 20px; font-weight: bold;"><?php esc_html_e( 'Offer the following filters. This filter passes the html that is generated.', 'infinite-all-images' ); ?></div>
			<div style="display:block; padding: 5px 35px;">
			<code>post_infiniteallimages</code>
			</div>
		</details>

		<details style="margin-bottom: 5px;">
		<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php esc_html_e( 'Caution:' ); ?></strong></summary>
			<div style="padding: 5px 20px; font-weight: bold;"><?php esc_html_e( 'If a hyphen and a number, such as "-2", are placed at the end of the page slug, the page transition will stop in the middle.', 'infinite-all-images' ); ?></div>
		</details>

		<details style="margin-bottom: 5px;" open>
		<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php esc_html_e( 'Settings' ); ?></strong></summary>
		<div class="wrap">
			<form method="post" action="<?php echo esc_url( $scriptname ); ?>">
			<?php wp_nonce_field( 'iai_settings', 'iai_tabs' ); ?>

			<div class="submit">
				<?php submit_button( __( 'Save Changes' ), 'large', 'IaiSave', false ); ?>
				<?php submit_button( __( 'Default' ), 'large', 'Default', false ); ?>
			</div>

			<div style="width: 100%; height: 100%; margin: 5px; padding: 5px; border: #CCC 2px solid;">

				<div style="display: block; padding:5px 5px;">
					<h3><?php esc_html_e( 'Display', 'infinite-all-images' ); ?></h3>
					<?php
					if ( current_user_can( 'manage_options' ) ) {
						?>
					<div style="display: block; padding:5px 20px;">
						<?php esc_html_e( 'Displays images of all users:', 'infinite-all-images' ); ?>
						<input type="checkbox" name="infiniteallimages_allusers" value="1" <?php checked( '1', $infiniteallimages_option['allusers'] ); ?> />
					</div>
						<?php
					}
					?>
					<div style="display: block; padding:5px 20px;">
					<?php esc_html_e( 'Image&acute;s Size:', 'infinite-all-images' ); ?>
					<select id="infiniteallimages_img_size" name="infiniteallimages_img_size">
						<option 
						<?php
						if ( 'full' == $infiniteallimages_option['img_size'] ) {
							echo 'selected="selected"';}
						?>
						>full</option>
						<option 
						<?php
						if ( 'thumbnail' == $infiniteallimages_option['img_size'] ) {
							echo 'selected="selected"';}
						?>
						>thumbnail</option>
						<option 
						<?php
						if ( 'medium' == $infiniteallimages_option['img_size'] ) {
							echo 'selected="selected"';}
						?>
						>medium</option>
						<option 
						<?php
						if ( 'large' == $infiniteallimages_option['img_size'] ) {
							echo 'selected="selected"';}
						?>
						>large</option>
					</select>
					</div>
					<div style="display: block; padding:5px 20px;">
					<?php esc_html_e( 'Number of items per page:' ); ?><input type="number" step="1" min="1" max="99" maxlength="2" class="screen-per-page" name="infiniteallimages_display" value="<?php echo esc_attr( intval( $infiniteallimages_option['display'] ) ); ?>" style="width: 80px;" />
					</div>
					<div style="display: block; padding:5px 20px;">
					<?php esc_html_e( 'Width of one column of the image:', 'infinite-all-images' ); ?><input type="number" step="1" min="10" max="999" maxlength="3" class="screen-per-page" name="infiniteallimages_width" value="<?php echo esc_attr( intval( $infiniteallimages_option['width'] ) ); ?>" style="width: 80px;" />px
					</div>
					<div style="display: block; padding:5px 20px;">
					<?php esc_html_e( 'Margin between images:', 'infinite-all-images' ); ?><input type="number" step="1" min="1" max="99" maxlength="2" class="screen-per-page" name="infiniteallimages_margin" value="<?php echo esc_attr( intval( $infiniteallimages_option['margin'] ) ); ?>" style="width: 80px;" />px
					</div>
					<div style="display: block; padding:5px 20px;">
					<?php esc_html_e( 'Type of Sort:', 'infinite-all-images' ); ?>
					<select id="infiniteallimages_sort" name="infiniteallimages_sort">
						<option 
						<?php
						if ( 'new' == $infiniteallimages_option['sort'] ) {
							echo 'selected="selected"';}
						?>
						>new</option>
						<option 
						<?php
						if ( 'old' == $infiniteallimages_option['sort'] ) {
							echo 'selected="selected"';}
						?>
						>old</option>
						<option 
						<?php
						if ( 'des' == $infiniteallimages_option['sort'] ) {
							echo 'selected="selected"';}
						?>
						>des</option>
						<option 
						<?php
						if ( 'asc' == $infiniteallimages_option['sort'] ) {
							echo 'selected="selected"';}
						?>
						>asc</option>
					</select>
					</div>
					<div style="display: block; padding:5px 20px;">
						<?php esc_html_e( 'If the image is attached to the post, to link to the post URL:', 'infinite-all-images' ); ?>
						<input type="checkbox" name="infiniteallimages_parent" value="1" <?php checked( '1', $infiniteallimages_option['parent'] ); ?> />
					</div>
					<div style="display: block; padding:5px 20px;">
						<?php esc_html_e( 'loading_image:', 'infinite-all-images' ); ?>
						<input type="text" style="width: 80%;"name="infiniteallimages_loading_image" value="<?php echo esc_attr( $infiniteallimages_option['loading_image'] ); ?>" />
					</div>
				</div>
			</div>

			<div style="width: 100%; height: 100%; margin: 5px; padding: 5px; border: #CCC 2px solid;">
				<div style="display: block; padding:5px 5px;">
					<h3><?php esc_html_e( 'Exclude', 'infinite-all-images' ); ?> ID</h3>
					<div style="display: block; padding:5px 20px;">
					<?php
					esc_html_e( 'Specifies a comma-separated list to exclusion media ID:', 'infinite-all-images' );
					if ( ! empty( $infiniteallimages_option['exclude_id'] ) ) {
						$exclude_id = implode( ',', $infiniteallimages_option['exclude_id'] );
					} else {
						$exclude_id = null;
					}
					?>
					<textarea name="infiniteallimages_exclude_id" style="width: 100%;"><?php echo esc_textarea( $exclude_id ); ?></textarea>
						<div>
						<?php
						$medialibrary_html = '<a href="' . admin_url( 'upload.php' ) . '" target="_blank" rel="noopener noreferrer" style="text-decoration: none; word-break: break-all;">' . __( 'Media Library' ) . '</a>';
						/* translators: Media library link */
						echo wp_kses_post( sprintf( __( 'When you activate this plugin, will be displayed ID is in the column of the %1$s', 'infinite-all-images' ), $medialibrary_html ) );
						?>
						</div>
					</div>
				</div>
			</div>

			<div style="width: 100%; height: 100%; margin: 5px; padding: 5px; border: #CCC 2px solid;">
				<div style="display: block; padding:5px 5px;">
					<h3><?php esc_html_e( 'Term filter', 'infinite-all-images' ); ?> <?php esc_html_e( 'Slug' ); ?></h3>
					<div style="display: block; padding:5px 20px;">
					<?php
					esc_html_e( 'Specifies the slug of the term to be filtered by term:', 'infinite-all-images' );
					if ( ! empty( $infiniteallimages_option['term_filter'] ) ) {
						$term_filter = $infiniteallimages_option['term_filter'];
					} else {
						$term_filter = null;
					}
					?>
					<input type="text" name="infiniteallimages_term_filter" value="<?php echo esc_attr( $term_filter ); ?>">
					</div>
				</div>
			</div>

			<?php submit_button( __( 'Save Changes' ), 'large', 'IaiSave', true ); ?>

			</form>
		</div>
		</details>

		</div>
		<?php
	}

	/** ==================================================
	 * Credit
	 *
	 * @since 1.00
	 */
	private function credit() {

		$plugin_name    = null;
		$plugin_ver_num = null;
		$plugin_path    = plugin_dir_path( __DIR__ );
		$plugin_dir     = untrailingslashit( wp_normalize_path( $plugin_path ) );
		$slugs          = explode( '/', $plugin_dir );
		$slug           = end( $slugs );
		$files          = scandir( $plugin_dir );
		foreach ( $files as $file ) {
			if ( '.' === $file || '..' === $file || is_dir( $plugin_path . $file ) ) {
				continue;
			} else {
				$exts = explode( '.', $file );
				$ext  = strtolower( end( $exts ) );
				if ( 'php' === $ext ) {
					$plugin_datas = get_file_data(
						$plugin_path . $file,
						array(
							'name'    => 'Plugin Name',
							'version' => 'Version',
						)
					);
					if ( array_key_exists( 'name', $plugin_datas ) && ! empty( $plugin_datas['name'] ) && array_key_exists( 'version', $plugin_datas ) && ! empty( $plugin_datas['version'] ) ) {
						$plugin_name    = $plugin_datas['name'];
						$plugin_ver_num = $plugin_datas['version'];
						break;
					}
				}
			}
		}
		$plugin_version = __( 'Version:' ) . ' ' . $plugin_ver_num;
		/* translators: FAQ Link & Slug */
		$faq       = sprintf( __( 'https://wordpress.org/plugins/%s/faq', 'infinite-all-images' ), $slug );
		$support   = 'https://wordpress.org/support/plugin/' . $slug;
		$review    = 'https://wordpress.org/support/view/plugin-reviews/' . $slug;
		$translate = 'https://translate.wordpress.org/projects/wp-plugins/' . $slug;
		$facebook  = 'https://www.facebook.com/katsushikawamori/';
		$twitter   = 'https://twitter.com/dodesyo312';
		$youtube   = 'https://www.youtube.com/channel/UC5zTLeyROkvZm86OgNRcb_w';
		$donate    = __( 'https://shop.riverforest-wp.info/donate/', 'infinite-all-images' );

		?>
		<span style="font-weight: bold;">
		<div>
		<?php echo esc_html( $plugin_version ); ?> | 
		<a style="text-decoration: none;" href="<?php echo esc_url( $faq ); ?>" target="_blank" rel="noopener noreferrer">FAQ</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $support ); ?>" target="_blank" rel="noopener noreferrer">Support Forums</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $review ); ?>" target="_blank" rel="noopener noreferrer">Reviews</a>
		</div>
		<div>
		<a style="text-decoration: none;" href="<?php echo esc_url( $translate ); ?>" target="_blank" rel="noopener noreferrer">
		<?php
		/* translators: Plugin translation link */
		echo esc_html( sprintf( __( 'Translations for %s' ), $plugin_name ) );
		?>
		</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $facebook ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-facebook"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-twitter"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $youtube ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-video-alt3"></span></a>
		</div>
		</span>

		<div style="width: 250px; height: 180px; margin: 5px; padding: 5px; border: #CCC 2px solid;">
		<h3><?php esc_html_e( 'Please make a donation if you like my work or would like to further the development of this plugin.', 'infinite-all-images' ); ?></h3>
		<div style="text-align: right; margin: 5px; padding: 5px;"><span style="padding: 3px; color: #ffffff; background-color: #008000">Plugin Author</span> <span style="font-weight: bold;">Katsushi Kawamori</span></div>
		<button type="button" style="margin: 5px; padding: 5px;" onclick="window.open('<?php echo esc_url( $donate ); ?>')"><?php esc_html_e( 'Donate to this plugin &#187;' ); ?></button>
		</div>

		<?php
	}

	/** ==================================================
	 * Settings register
	 *
	 * @since 1.00
	 */
	public function register_settings() {

		/* Old option 2.14 -> New option 2.15 */
		if ( get_option( 'infinite_all_images_' . get_current_user_id() ) ) {
			update_user_option( get_current_user_id(), 'infiniteallimages', get_option( 'infinite_all_images_' . get_current_user_id() ) );
			delete_option( 'infinite_all_images_' . get_current_user_id() );
		}

		if ( get_user_option( 'infiniteallimages', get_current_user_id() ) ) {
			$infinite_all_images_settings = get_user_option( 'infiniteallimages', get_current_user_id() );
			/* 2.19 -> 3.00 */
			if ( array_key_exists( 'exif_text', $infinite_all_images_settings ) ) {
				unset( $infinite_all_images_settings['exif_text'] );
				update_user_option( get_current_user_id(), 'infiniteallimages', $infinite_all_images_settings );
			}
			/* 2.19 -> 3.00 */
			if ( array_key_exists( 'character_code', $infinite_all_images_settings ) ) {
				unset( $infinite_all_images_settings['character_code'] );
				update_user_option( get_current_user_id(), 'infiniteallimages', $infinite_all_images_settings );
			}
		} else {
			$infinite_all_images_tbl = array(
				'allusers' => false,
				'img_size' => 'full',
				'display' => 20,
				'width' => 100,
				'margin' => 1,
				'sort' => 'new',
				'exclude_id' => null,
				'term_filter' => null,
				'parent' => true,
				'loading_image' => plugin_dir_url( __DIR__ ) . '/img/ajax-loader.gif',
			);
			update_user_option( get_current_user_id(), 'infiniteallimages', $infinite_all_images_tbl );
		}
	}

	/** ==================================================
	 * Update wp_options table.
	 *
	 * @since 1.00
	 */
	private function options_updated() {

		if ( isset( $_POST['Default'] ) && ! empty( $_POST['Default'] ) ) {
			if ( check_admin_referer( 'iai_settings', 'iai_tabs' ) ) {
				$infinite_all_images_tbl = array(
					'allusers' => false,
					'img_size' => 'full',
					'display' => 20,
					'width' => 100,
					'margin' => 1,
					'sort' => 'new',
					'exclude_id' => '',
					'term_filter' => '',
					'parent' => true,
					'loading_image' => plugin_dir_url( __DIR__ ) . 'img/ajax-loader.gif',
				);
				update_user_option( get_current_user_id(), 'infiniteallimages', $infinite_all_images_tbl );
				echo '<div class="notice notice-success is-dismissible"><ul><li>' . esc_html( __( 'Settings' ) . ' --> ' . __( 'Default' ) . ' --> ' . __( 'Changes saved.' ) ) . '</li></ul></div>';
			}
		}

		if ( isset( $_POST['IaiSave'] ) && ! empty( $_POST['IaiSave'] ) ) {
			if ( check_admin_referer( 'iai_settings', 'iai_tabs' ) ) {
				$infinite_all_images_tbl = get_user_option( 'infiniteallimages', get_current_user_id() );
				if ( ! empty( $_POST['infiniteallimages_allusers'] ) ) {
					$infinite_all_images_tbl['allusers'] = intval( $_POST['infiniteallimages_allusers'] );
				} else {
					$infinite_all_images_tbl['allusers'] = false;
				}
				if ( ! empty( $_POST['infiniteallimages_img_size'] ) ) {
					$infinite_all_images_tbl['img_size'] = sanitize_text_field( wp_unslash( $_POST['infiniteallimages_img_size'] ) );
				}
				if ( ! empty( $_POST['infiniteallimages_display'] ) ) {
					$infinite_all_images_tbl['display'] = intval( $_POST['infiniteallimages_display'] );
				}
				if ( ! empty( $_POST['infiniteallimages_width'] ) ) {
					$infinite_all_images_tbl['width'] = intval( $_POST['infiniteallimages_width'] );
				}
				if ( ! empty( $_POST['infiniteallimages_margin'] ) ) {
					$infinite_all_images_tbl['margin'] = intval( $_POST['infiniteallimages_margin'] );
				}
				if ( ! empty( $_POST['infiniteallimages_sort'] ) ) {
					$infinite_all_images_tbl['sort'] = sanitize_text_field( wp_unslash( $_POST['infiniteallimages_sort'] ) );
				}
				if ( ! empty( $_POST['infiniteallimages_exclude_id'] ) ) {
					$infinite_all_images_tbl['exclude_id'] = explode( ',', sanitize_text_field( wp_unslash( $_POST['infiniteallimages_exclude_id'] ) ) );
				} else {
					$infinite_all_images_tbl['exclude_id'] = null;
				}
				if ( ! empty( $_POST['infiniteallimages_term_filter'] ) ) {
					$infinite_all_images_tbl['term_filter'] = sanitize_text_field( wp_unslash( $_POST['infiniteallimages_term_filter'] ) );
				} else {
					$infinite_all_images_tbl['term_filter'] = null;
				}
				if ( ! empty( $_POST['infiniteallimages_parent'] ) ) {
					$infinite_all_images_tbl['parent'] = intval( $_POST['infiniteallimages_parent'] );
				} else {
					$infinite_all_images_tbl['parent']  = false;
				}
				if ( ! empty( $_POST['infiniteallimages_loading_image'] ) ) {
					$infinite_all_images_tbl['loading_image'] = sanitize_text_field( wp_unslash( $_POST['infiniteallimages_loading_image'] ) );
				}
				update_user_option( get_current_user_id(), 'infiniteallimages', $infinite_all_images_tbl );
				echo '<div class="notice notice-success is-dismissible"><ul><li>' . esc_html( __( 'Settings' ) . ' --> ' . __( 'Changes saved.' ) ) . '</li></ul></div>';
			}
		}
	}

	/** ==================================================
	 * Posts columuns id
	 *
	 * @param array $defaults  defaults.
	 * @since 1.00
	 */
	public function posts_columns_attachment_id( $defaults ) {
		global $pagenow;
		if ( 'upload.php' == $pagenow ) {
			$defaults['iai_post_attachments_id'] = 'ID';
		}
		return $defaults;
	}

	/** ==================================================
	 * Posts custom columuns id
	 *
	 * @param string $column_name  column_name.
	 * @param int    $id  id.
	 * @since 1.00
	 */
	public function posts_custom_columns_attachment_id( $column_name, $id ) {
		if ( 'iai_post_attachments_id' === $column_name ) {
			echo esc_html( $id );
		}
	}
}


