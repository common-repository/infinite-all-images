<?php
/**
 * Infinite All Images
 *
 * @package    InfiniteAllImages
 * @subpackage InfiniteAllImages Main Functions
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

$infiniteallimages = new InfiniteAllImages();

/** ==================================================
 * Main Functions
 */
class InfiniteAllImages {

	/** ==================================================
	 * Loading image
	 *
	 * @var $loading_image  loading_image.
	 */
	private $loading_image;

	/** ==================================================
	 * Width
	 *
	 * @var $width  width.
	 */
	private $width;

	/** ==================================================
	 * Margin
	 *
	 * @var $margin  margin.
	 */
	private $margin;

	/** ==================================================
	 * Maxpage
	 *
	 * @var $maxpage  maxpage.
	 */
	private $maxpage;

	/** ==================================================
	 * Construct
	 *
	 * @since 2.07
	 */
	public function __construct() {

		add_action( 'wp_print_styles', array( $this, 'load_styles' ) );
		add_shortcode( 'iai', array( $this, 'infiniteallimages_func' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_scripts' ) );
		add_action( 'wp_footer', array( $this, 'load_localize_scripts_styles' ) );
	}

	/** ==================================================
	 * Main short code
	 *
	 * @param array  $atts  atts.
	 * @param string $html  html.
	 * @return string $html
	 * @since 1.00
	 */
	public function infiniteallimages_func( $atts, $html = null ) {

		$author_id = get_post_field( 'post_author', get_the_ID() );

		$infiniteallimages_option = get_user_option( 'infiniteallimages', $author_id );
		if ( ! $infiniteallimages_option ) {
			return;
		}

		$a = shortcode_atts(
			array(
				'img_size' => '',
				'display' => '',
				'width' => '',
				'margin' => '',
				'sort' => '',
				'exclude_id' => '',
				'term_filter' => '',
				'parent' => '',
				'loading_image' => '',
			),
			$atts
		);

		$img_size = $a['img_size'];
		$display = $a['display'];
		$width = $a['width'];
		$margin = $a['margin'];
		$sort = $a['sort'];
		$exclude_id = $a['exclude_id'];
		$term_filter = $a['term_filter'];
		$parent = $a['parent'];
		$loading_image = $a['loading_image'];

		if ( empty( $display ) ) {
			$display = intval( $infiniteallimages_option['display'] );
			$display = apply_filters( 'iai_display', $display );
		}
		if ( empty( $width ) ) {
			$width = intval( $infiniteallimages_option['width'] );
			$width = apply_filters( 'iai_width', $width );
		}
		if ( empty( $margin ) ) {
			$margin = intval( $infiniteallimages_option['margin'] );
			$margin = apply_filters( 'iai_margin', $margin );
		}
		if ( empty( $parent ) ) {
			$parent = $infiniteallimages_option['parent'];
			$parent = apply_filters( 'iai_parent', $parent );
		}
		if ( empty( $loading_image ) ) {
			$loading_image = $infiniteallimages_option['loading_image'];
			$loading_image = apply_filters( 'iai_loading_image', $loading_image );
		}

		$nonce = null;
		$page = null;
		if ( isset( $_GET['_wpnonce'] ) && ! empty( $_GET['_wpnonce'] ) ) {
			$nonce = sanitize_key( $_GET['_wpnonce'] );
		}
		if ( false !== wp_verify_nonce( $nonce, 'iai_nonce' ) ) {
			if ( ! empty( $_GET['p_iai'] ) ) {
				$page = intval( $_GET['p_iai'] ); /* pages */
			}
		}

		if ( empty( $img_size ) ) {
			$img_size = $infiniteallimages_option['img_size'];
		} else if ( 'full' === $img_size || 'thumbnail' === $img_size || 'medium' === $img_size || 'large' === $img_size ) {
			$dummy = 0; /* skip */
		} else {
			$img_size = 'full';
		}
		$img_size = apply_filters( 'iai_img_size', $img_size );

		/* term filter*/
		if ( empty( $term_filter ) ) {
			$term_filter = $infiniteallimages_option['term_filter'];
		} else {
			$term_filter = explode( ',', sanitize_text_field( $term_filter ) );
		}
		$term_filter = apply_filters( 'iai_term_filter', $term_filter );
		$taxonomy_names = get_object_taxonomies( 'attachment', 'names' );
		$term_args = array();
		if ( ! empty( $term_filter ) ) {
			foreach ( $taxonomy_names as $taxonomy_name ) {
				if ( term_exists( $term_filter, $taxonomy_name ) ) {
					$term_args[] = array(
						'taxonomy' => $taxonomy_name,
						'field' => 'slug',
						'terms' => $term_filter,
					);
					break;
				}
			}
		}

		/* authors filter*/
		$authors = array();
		$allusers = $infiniteallimages_option['allusers'];
		$allusers = apply_filters( 'iai_allusers', $allusers );
		if ( $allusers ) {
			$users = get_users();
			foreach ( $users as $user ) {
				$authors[] = $user->ID;
			}
		} else {
			$authors[] = $author_id;
		}

		/* not ID filter*/
		if ( empty( $exclude_id ) ) {
			$exclude_id = $infiniteallimages_option['exclude_id'];
		} else {
			$exclude_id = explode( ',', sanitize_text_field( $exclude_id ) );
		}
		$exclude_id = apply_filters( 'iai_exclude_id', $exclude_id );

		/* mime filter*/
		$mimes = get_allowed_mime_types();
		$ext_types = wp_get_ext_types();
		$ext_image_types = $ext_types['image'];
		$post_mime_type = array();
		foreach ( $mimes as $type => $mime ) {
			$types = explode( '|', $type );
			foreach ( $types as $value ) {
				if ( in_array( $value, $ext_image_types ) ) {
					$post_mime_type[] = $mime;
				}
			}
		}
		$post_mime_type = apply_filters( 'iai_post_mime_type', $post_mime_type );

		/* sort filter*/
		if ( empty( $sort ) ) {
			$sort = $infiniteallimages_option['sort'];
			$sort = apply_filters( 'iai_sort', $sort );
		}
		$sort_key = null;
		$sort_order = null;
		if ( 'new' === $sort ) {
			$sort_key = 'post_date';
			$sort_order = 'DESC';
		} else if ( 'old' === $sort ) {
			$sort_key = 'post_date';
			$sort_order = 'ASC';
		} else if ( 'des' === $sort ) {
			$sort_key = 'post_title';
			$sort_order = 'DESC';
		} else if ( 'asc' === $sort ) {
			$sort_key = 'post_title';
			$sort_order = 'ASC';
		} else {
			$sort_key = 'post_date';
			$sort_order = 'DESC';
		}

		$args = array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'tax_query'      => $term_args,
			'author__in'     => $authors,
			'post__not_in'   => $exclude_id,
			'post_mime_type' => $post_mime_type,
			'posts_per_page' => -1,
			'orderby'        => $sort_key,
			'order'          => $sort_order,
		);
		$attachments = get_posts( $args );
		$files = $this->scan_media( $attachments, $img_size );
		unset( $attachments );

		if ( ! empty( $files ) ) {

			$maxpage = ceil( count( $files ) / $display );
			if ( empty( $page ) ) {
				$page = 1;
			}

			$this->loading_image = $loading_image;
			$this->maxpage = $maxpage;
			$this->width = $width;
			$this->margin = $margin;

			$beginfiles = 0;
			$endfiles = 0;
			if ( $page == $maxpage ) {
				$beginfiles = $display * ( $page - 1 );
				$endfiles = count( $files ) - 1;
			} else {
				$beginfiles = $display * ( $page - 1 );
				$endfiles = ( $display * $page ) - 1;
			}

			$linkfiles = null;
			if ( $files ) {
				for ( $i = $beginfiles; $i <= $endfiles; $i++ ) {
					$linkfile = $this->print_file( $files[ $i ]['imgurl'], $files[ $i ]['title'], $files[ $i ]['thumburl'], $files[ $i ]['parent_id'], $parent );
					$linkfiles = $linkfiles . $linkfile;
				}
			}

			$linkpages = null;
			$linkpages = $this->print_pages( $page, $maxpage );

			$linkfiles_begin = null;
			$linkfiles_end = null;
			$linkpages_begin = null;
			$linkpages_end = null;
			$sortlink_begin = null;
			$sortlink_end = null;
			$searchform_begin = null;
			$searchform_end = null;

			$linkfiles_begin = '<div id="infiniteallimages">';
			$linkfiles_end = '</div><div style="clear: both;"></div>';
			$linkpages_begin = '<div style="width: 100%; text-align: center;">';
			$linkpages_end = '</div>';

			$html .= '<div>';
			$html .= $linkfiles_begin;
			$html .= $linkfiles;
			$html .= $linkfiles_end;

			$html .= $linkpages_begin;
			$html .= $linkpages;
			$html .= $linkpages_end;
			$html .= '</div>';

			$html = apply_filters( 'post_infiniteallimages', $html );

			return $html;

		}
	}

	/** ==================================================
	 * Scan media
	 *
	 * @param array  $attachments  attachments.
	 * @param string $img_size  img_size.
	 * @return array $files
	 * @since 1.00
	 */
	private function scan_media( $attachments, $img_size ) {

		$filecount = 0;
		$files = array();
		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$title = $attachment->post_title;
				$imgurl = null;
				$thumburl = null;
				$img_src = wp_get_attachment_image_src( $attachment->ID, 'full', false );
				$thumb_src = wp_get_attachment_image_src( $attachment->ID, $img_size, false );
				$imgurl = $img_src[0];
				$thumburl = $thumb_src[0];
				$files[ $filecount ]['imgurl'] = $imgurl;
				$files[ $filecount ]['title'] = $title;
				$files[ $filecount ]['thumburl'] = $thumburl;
				$files[ $filecount ]['parent_id'] = $attachment->post_parent;
				++$filecount;
			}
		}

		return $files;
	}

	/** ==================================================
	 * Print file
	 *
	 * @param string $imgurl  imgurl.
	 * @param string $title  title.
	 * @param string $thumburl  thumburl.
	 * @param int    $parent_id  parent_id.
	 * @param bool   $parent  parent.
	 * @return string $linkfile
	 * @since 1.00
	 */
	private function print_file( $imgurl, $title, $thumburl, $parent_id, $parent ) {

		$parent_title = null;
		if ( $parent && $parent_id > 0 ) {
			$parent_title = get_the_title( $parent_id );
			$imglink = get_permalink( $parent_id );
		} else {
			$imglink = $imgurl;
		}

		$titles = $parent_title . ' ' . $title;

		$linkfile = null;
		if ( is_single() || is_page() ) {
			$linkfile = '<a href="' . $imglink . '" title="' . $titles . '"><img src="' . $thumburl . '" alt="' . $title . '" title="' . $titles . '" class="infiniteallimagesitem"></a>';
		} else {
			$linkfile = '<a href="' . $imglink . '" title="' . $titles . '"><img src="' . $thumburl . '" alt="' . $title . '" title="' . $titles . '"></a>';
		}

		return $linkfile;
	}

	/** ==================================================
	 * Print pages
	 *
	 * @param int $page  page.
	 * @param int $maxpage  maxpage.
	 * @return string $linkpages  linkpages.
	 * @since 1.00
	 */
	private function print_pages( $page, $maxpage ) {

		$query = get_permalink();
		$new_query = add_query_arg( array( 'p_iai' => $page + 1 ), $query );
		$new_query = wp_nonce_url( $new_query, 'iai_nonce' );

		$linkpages = null;

		if ( is_single() || is_page() ) {
			if ( $page >= 1 && $maxpage > $page ) {
				$linkpages = '<div class="infiniteallimages-nav"><a rel="next" href="' . $new_query . '"></a><span class="dashicons dashicons-arrow-down-alt"></span></div>';
			}
		}

		return $linkpages;
	}

	/** ==================================================
	 * Load Script
	 *
	 * @since 2.00
	 */
	public function load_frontend_scripts() {
		if ( is_single() || is_page() ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-masonry' );
			wp_enqueue_script( 'infinitescroll', plugin_dir_url( __DIR__ ) . 'js/jquery.infinitescroll.min.js', null, '2.1.0' );
		}
	}

	/** ==================================================
	 * Load Localize Script and Style
	 *
	 * @since 2.00
	 */
	public function load_localize_scripts_styles() {

		if ( ( is_single() || is_page() ) && ! is_null( $this->width ) ) {
			wp_enqueue_script( 'infiniteallimages-jquery', plugin_dir_url( __DIR__ ) . 'js/jquery.infiniteallimages.js', array( 'jquery' ), '1.00', false );
			$localize_iai_settings = array(
				'loading_image' => $this->loading_image,
				'maxpage'       => $this->maxpage,
			);
			wp_localize_script( 'infiniteallimages-jquery', 'iai_settings', $localize_iai_settings );
			wp_enqueue_style( 'infiniteallimages', plugin_dir_url( __DIR__ ) . 'css/infiniteallimages.css', array(), '1.00' );
			$css = '.infiniteallimagesitem { width: ' . $this->width . 'px; } #infiniteallimages img{ margin: ' . $this->margin . 'px; }
';
			wp_add_inline_style( 'infiniteallimages', $css );
		} else {
			wp_enqueue_style( 'infiniteallimages', plugin_dir_url( __DIR__ ) . 'css/infiniteallimages.dummy.css', array(), '1.00' );
		}
	}

	/** ==================================================
	 * Load Dashicons
	 *
	 * @since 1.00
	 */
	public function load_styles() {
		wp_enqueue_style( 'dashicons' );
	}
}
