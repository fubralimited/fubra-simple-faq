<?php
/*
Plugin Name: Fubra Simple FAQ
Description: Adds an FAQ post type and taxonomy to admin menus.
Version: 0.2
Author: ConfuzzledDuck
*/

if ( !class_exists( 'Fubra_Simple_Faq' ) ) {

	class Fubra_Simple_Faq {
	
		protected $_post_type_name = 'faq';
		protected $_taxonomy_name = 'faq_cat';

		public function __construct() {

			// Actions...
			add_action( 'init', array( $this, 'register_post_types' ) );
			add_action( 'save_post', array( $this, 'save_faq' ) );
			add_action( 'created_'.$this->_taxonomy_name, array( $this, 'save_category' ) );
			
			// Filters...
			add_filter( 'generate_rewrite_rules', array( $this, 'slug_rewrite' ) );
		
		}
		
		public function register_post_types() {
		
			register_post_type( $this->_post_type_name, array(
					'labels' => array(
						'name' => _x( 'FAQ', 'post type general name' ),
						'singular_name' => _x( 'FAQ', 'post type singular name' ),
						'add_new' => _x( 'Add New', 'book' ),
						'add_new_item' => __( 'Add New FAQ' ),
						'edit_item' => __( 'Edit FAQ' ),
						'new_item' => __( 'New FAQ Items' ),
						'all_items' => __( 'All FAQs' ),
						'view_item' => __( 'View FAQ' ),
						'search_items' => __( 'Search FAQ' ),
						'not_found' => __( 'No FAQ Items found' ),
						'not_found_in_trash' => __( 'No FAQ Items found in the Trash' ),
						'parent_item_colon' => '',
						'menu_name' => 'FAQ'
					),
					'description' => 'Holds FAQ specific data',
					'public' => true,
					'show_ui' => true,
					'show_in_menu' => true,
					'query_var' => true,
					'rewrite' => array( 'slug' => 'faq' ),
					'capability_type' => 'post',
					'has_archive' => true,
					'hierarchical' => false,
					'menu_position' => 5,
					'supports' => array( 'title', 'editor' ),
					'menu_icon' => 'dashicons-welcome-write-blog'
				) );


			// Add new taxonomy, make it hierarchical (like categories)
			register_taxonomy( $this->_taxonomy_name, array( 'faq' ), array(
				'hierarchical' => true,
				'labels' => array(
					'name' => _x( 'FAQ Categories', 'taxonomy general name' ),
					'singular_name' => _x( 'FAQ Category', 'taxonomy singular name' ),
					'search_items' =>  __( 'Search FAQ Categories' ),
					'all_items' => __( 'All FAQ Category' ),
					'parent_item' => __( 'Parent FAQ Category' ),
					'parent_item_colon' => __( 'Parent FAQ Category:' ),
					'edit_item' => __( 'Edit FAQ Category' ),
					'update_item' => __( 'Update FAQ Category' ),
					'add_new_item' => __( 'Add New FAQ Category' ),
					'new_item_name' => __( 'New FAQ Category Name' ),
					'menu_name' => __( 'FAQ Category' ),
				),
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => array( 'slug' => 'faq' )
			));
		
		}
		
		// When an FAQ is saved we want to force flush the rewrite rules to allow both
		// post and taxonomy to share the 'faq' slug...
		public function save_faq( $post_id, $post = null ) {
		
			if ( !isset( $post ) ) {
				$post = get_post( $post_id, 'OBJECT' );
			}
		
			if ( $post->post_type == $this->_post_type_name ) {
				flush_rewrite_rules();
			}
		
		}
		
		// When a new term is added to the FAQ taxonomy we want to force flush the
		// rewrite rules to allow both post and taxonomy to share the 'faq' slug...
		public function save_category() {
		
			flush_rewrite_rules();
		
		}
		
		public function slug_rewrite( $wp_rewrite ) {
		
			$rules = array();
			
			// Get all custom post types and taxonomies...
			$taxonomies = get_taxonomies( array( '_builtin' => false ), 'objects' );
			$post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' );

			foreach ( $post_types as $post_type ) {
				foreach ( $taxonomies as $taxonomy ) {
					foreach ( $taxonomy->object_type AS $object_type ) {
						if ( $object_type == $post_type->rewrite['slug'] ) {
							$terms = get_categories( array( 'type' => $object_type, 'taxonomy' => $taxonomy->name, 'hide_empty' => 0 ) );
							foreach ( $terms AS $term ) {
								$rules[$object_type . '/' . $term->slug . '/?$'] = 'index.php?' . $term->taxonomy . '=' . $term->slug;
							}
						}
					}
				}
			}
			
			// Merge compiled rules with WP's default rules...
			$wp_rewrite->rules = $rules + $wp_rewrite->rules;
		
		}
	
	}
	
	// Create an instance of the class...
	$fubra_simple_faq = new Fubra_Simple_Faq();

}