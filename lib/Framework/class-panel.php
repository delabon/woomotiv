<?php 

namespace WooMotiv\Framework;

class Panel{

    public $tabs = array();
    private $page_slug = '';
    private $admin_url = '';
    private $template_path = '';

    /**
     * Constructor
     */
    function __construct( $page_slug, $template_path ){                    
        $this->page_slug = $page_slug;
        $this->template_path = $template_path;
        $this->admin_url = admin_url( 'admin.php?page=' . $this->page_slug );
    }

    /**
     * Load Assets
     */
    static function load_assets( $base_url ){

        wp_enqueue_style( 'wp-color-picker' );

        wp_enqueue_script(
            'dlbv2_panel', 
            $base_url . '/js/panel.js', 
            array('jquery', 'wp-color-picker'), 
            '2.0.1', 
            true 
        );

        wp_enqueue_style( 
            'dlbv2_panel', 
            $base_url . '/css/panel.css', 
            array(), 
            '2.0.1' 
        );

        if( is_rtl() ){
            
            wp_enqueue_style( 
                'dlb_panel_rtl', 
                $base_url . '/css/panel-rtl.css', 
                array( 'dlbv2_panel' ), 
                '2.0.1' 
            );

        }

    }

    /**
     * Search if tab exists
     * @param string $slug
     */
    function isExists( $slug ){

        foreach( $this->tabs as $tab_slug => $tab ){

            if( $tab_slug === $slug ){
                return $tab;
            }

            if( isset( $tab['children'] ) ){

                foreach( $tab['children'] as $tab_slug_child => $tab_child ){

                    if( $tab_slug_child === $slug ){
                        return $tab_child;
                    }
                }        
            }
        }

        return false;
    }

    /**
     * Add New Tab
     * @param string $name
     * @param string $slug
     */
    function addTab( $name, $slug, $content_path, $parent_slug = null ){

        if( $this->isExists( $slug ) ){
            throw new \Exception('Tab already registered');
        }

        // its a child tab
        if( $parent_slug ){
            if( isset( $this->tabs[ $parent_slug ] ) ){
                $this->tabs[ $parent_slug ]['children'][ $slug ] = array(
                    'slug' => $slug,
                    'name' => $name,
                    'content_path' => $content_path,
                    'isChild' => true,
                );
            }
        }
        else{
            $this->tabs[ $slug ] = array(
                'slug' => $slug,
                'name' => $name,
                'content_path' => $content_path,
                'parent_slug' => $parent_slug
            );  
        }
    }

    /**
     * @return array
     */
    function currentTab(){

        if( ! $this->count() ){
            throw new \Exception('No tabs were added!');            
        }

        $slug = isset( $_GET['tab'] ) ? $_GET['tab'] : '';       

        if( $tab = $this->isExists( $slug ) ){
            return $tab;         
        }

        return reset( $this->tabs ); // else return the first tab
    }

    /**
     * Count tabs
     * 
     * @return int
     */
    function count(){
        return count( $this->tabs );
    }

    /**
     * Returns the nav markup
     *
     * @return string
     */
    function navMarkup(){

        if( ! $this->count() ){
            return '';
        }

        $currentTab = $this->currentTab();
        $output = '<nav class="dlb_panel_nav"><ul>';

        foreach ( $this->tabs as $slug => $tab ) {
            
            $output .= '
                <li class="_tab ' . ( $currentTab['slug'] === $slug ? '_tab_active' : '' ) . ( isset( $tab['children'] ) ? ' _tab_has_children' : '' ) .'">
                    <a href="' . $this->admin_url . '&tab=' . $slug . '">' . $tab['name'] . '</a>';

            if( isset( $tab['children'] ) ){

                $output .= '<ul class="_sub_nav">';

                foreach ( $tab['children'] as $childslug => $childtab ) {
            
                    $output .= '
                        <li class="_tab ' . ( $currentTab['slug'] === $childslug ? '_tab_active' : '' ) . '">
                            <a href="' . $this->admin_url . '&tab=' . $childslug . '">' . $childtab['name'] . '</a>                                
                        </li>';
    
                }

                $output .= '</ul>';
            }

            $output .= '</li>';
        }

        $output .= '</ul></nav>';

        return $output;
    }

    /**
     * Print Panel
     */
    function print(){
        
        if( $this->count() == 0 ) return;
        
        $currentTab = $this->currentTab();
        $currentTabContent = require_once( $currentTab['content_path'] );
        $navMarkup = $this->navMarkup();

        if( file_exists( $this->template_path ) ){
            require_once $this->template_path;
        }
    }

}
