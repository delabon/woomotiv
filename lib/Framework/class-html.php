<?php 

namespace WooMotiv\Framework;

class HTML{

    static $attrs_pattern = '/([^\s]+)=[\"\'](.*?)[\"\']/i';

    /**
     * Return attributtes string
     * @param string $tag
     * @return array 
     */
    static function getAttributes( $tag ){

        $attrs = array();
        preg_match_all( self::$attrs_pattern, $tag, $matches );

        if( is_array( $matches ) && isset( $matches[1] ) ){

            $iii = 0;

            foreach( $matches[1] as $attr ){
                $attrs[ $attr ] = $matches[2][ $iii ];
                $iii += 1;
            }

        }

        return $attrs;
    }

    /**
     * Return tag properties string
     * @param array $atts
     * @return string 
     */
    static function tag_properties( $atts ){

        $attributesStr = '';
        
        foreach( $atts as $attr => $value ){

            if( is_array( $value ) ) {
                continue; // skip arrays
            }

            if( $value === null ){
                continue;
            }

            $attributesStr .= ' ' . $attr . '="' .$value. '"';
        }

        return $attributesStr;
    }

    /**
     * Remove unnecessary keys
     * @param array $atts by-reference
     * @param array $keys
     */
    static function removeAtts( & $atts, $keys ){

        foreach ( $keys as $key ) {
            unset( $atts[ $key ] );
        }
    }

    /**
     * Create title tag
     * @param string|null $title 
     * @param string $output by-reference
     * @param string $class 
     */
    static function titleTag( $title, & $output, $class ){

        if( ! $title ) return;

        $output = '<h3 class="' . $class . '_title">' . $title . '</h3>' . $output;
    }

    /**
     * Create description tag
     * @param string|null $description 
     * @param string $output by-reference
     * @param string $class 
     */
    static function descriptionTag( $description, & $output, $class ){

        if( ! $description ) return;

        $output .= '<p class="' . $class . '_description">' . $description . '</p>';
    }

    /**
     * Create description tag
     * @param string|null $description 
     * @param string $output by-reference
     * @param string $class 
     */
    static function wrapper( $condition, & $output, $class ){

        if( ! $condition ) return;

        $output = '<div class="' . $class . '">' . $output . '</div>';
    }

    /**
     * html input ( text,email,password,hidden,file ...)
     * @param array $atts
     * @return string
     */
    static function input( $atts = array() ){

        $atts = wp_parse_args( $atts, array(
            'type' => 'text',
            'id' => null,
            'name' => null,
            'value' => '',
            'title' => null,
            'description' => null,
            'class' => 'dlb_input',
            'wrapper' => true,
            'wrapper_class' => 'dlb_input_wrapper',
        ));

        extract( $atts );

        self::removeAtts( $atts, array( 'default', 'wrapper', 'wrapper_class', 'title', 'description' ));

        $output = '<input '.self::tag_properties( $atts ).'>';

        self::titleTag( $title, $output, $class );
        self::descriptionTag( $description, $output, $class );
        self::wrapper( $wrapper, $output, $wrapper_class );

        return $output; 
    }

    /**
     * html textarea
     * @param array $atts
     * @return string
     */
    static function textarea( $atts = array() ){

        $atts = wp_parse_args( $atts, array(
            'name' => null,
            'value' => '',
            'class' => 'dlb_input',
            'id' => null,
            'title' => null,
            'description' => null,
            'wrapper' => true,
            'wrapper_class' => 'dlb_input_wrapper',
        ));
        
        extract( $atts );

        self::removeAtts( $atts, array( 'default', 'wrapper', 'wrapper_class', 'type', 'title', 'description' ));


        $output = '<textarea '.self::tag_properties( $atts ).'>' . $value . '</textarea>';

        self::titleTag( $title, $output, $class );
        self::descriptionTag( $description, $output, $class );
        self::wrapper( $wrapper, $output, $wrapper_class );

        return $output; 
    }

    /**
     * html select
     * @param array $atts
     * @return string
     */
    static function select( $atts = array() ){

        $atts = wp_parse_args( $atts, array(
            'name' => null,
            'value' => '',
            'class' => 'dlb_input',
            'id' => null,
            'title' => null,
            'description' => null,
            'wrapper' => true,
            'wrapper_class' => 'dlb_input_wrapper',
            'items' => array()
        ));
                    
        extract( $atts );
        
        self::removeAtts( $atts, array( 'default', 'wrapper', 'wrapper_class', 'type', 'title', 'description', 'items' ));

        $output = '<select '.self::tag_properties( $atts ).'>';
        
        foreach( $items as $item_key => $item_name ){
            $output .= '<option value="'.$item_key.'" '.( $item_key == $value ? 'selected="selected"' : '' ).'>'.$item_name.'</option>';
        }

        $output .= '</select>';

        self::titleTag( $title, $output, $class );
        self::descriptionTag( $description, $output, $class );
        self::wrapper( $wrapper, $output, $wrapper_class );

        return $output; 
    }

    /**
     * html multiple select
     * @param array $atts
     * @return string
     */
    static function selectMultiple( $atts = array() ){

        $atts = wp_parse_args( $atts, array(
            'name' => null,
            'value' => '',
            'class' => 'dlb_input',
            'id' => null,
            'title' => null,
            'description' => null,
            'wrapper' => true,
            'wrapper_class' => 'dlb_input_wrapper',
            'items' => array()
        ));
                    
        extract( $atts );
        
        self::removeAtts( $atts, array( 'default', 'wrapper', 'wrapper_class', 'type', 'title', 'description', 'items' ));

        $selectedItems = explode(',', $value);

        $output = '<select class="'.$class.'" multiple>';
        
        foreach( $items as $item_key => $item_name ){

            $is_selected = false;

            foreach( $selectedItems as $selected_id ){
                $selected_id = trim( $selected_id );

                if( $item_key == $selected_id ){
                    $is_selected = true;
                }
            }

            $output .= '<option value="'.$item_key.'" '.( $is_selected ? 'selected="selected"' : '' ).'>'.$item_name.'</option>';
        }

        $output .= '</select>';

        $output .= self::input([
            'type' => 'hidden',
            'wrapper' => false,
            'value' => $value,
            'name' => $name,
            'id' => $id,
        ]);

        self::titleTag( $title, $output, $class );
        self::descriptionTag( $description, $output, $class );
        self::wrapper( $wrapper, $output, $wrapper_class );

        return $output; 
    }

    /**
     * html radio
     * @param array $atts
     * @return string
     */
    static function radio( $atts = array() ){
        
        $atts = wp_parse_args( $atts, array(
            'type' => 'text',
            'name' => null,
            'value' => '',
            'class' => 'dlb_input',
            'id' => null,
            'title' => null,
            'description' => null,
            'wrapper' => true,
            'wrapper_class' => 'dlb_input_wrapper',
            'items' => array(),
        ));

        extract( $atts );

        self::removeAtts( $atts, array( 'default', 'wrapper', 'wrapper_class', 'type', 'title', 'description', 'items', 'value' ));

        $output = '';
        
        foreach( $items as $item => $item_key ){
            $output .= '<label class="'.( $item == $value ? '_selected_' : '' ).'"><input type="radio" '.( $item == $value ? 'checked="checked"' : '' ).' '.self::tag_properties( $atts ).' value="'.$item.'" >';
            $output .= $item_key.'</label>';
        }

        self::titleTag( $title, $output, $class );
        self::descriptionTag( $description, $output, $class );
        self::wrapper( $wrapper, $output, $wrapper_class );

        return $output;
    }

    /**
     * html checkbox
     * @param array $items
     * @param array $atts
     * @return string
     */
    static function checkbox( $atts = array() ){
        
        $atts = wp_parse_args( $atts, array(
            'name' => null,
            'value' => 0,
            'id' => null,
            'class' => 'dlb_input',
            'text' => '',
            'title' => null,
            'description' => null,
            'wrapper' => true,
            'wrapper_class' => 'dlb_input_wrapper',
        ));

        extract( $atts );

        self::removeAtts( $atts, array( 'default', 'wrapper', 'wrapper_class', 'type', 'title', 'description', 'name', 'text' ));

        $output  = "<input value='{$value}' type='hidden' name='{$name}' >";        
        $output .= '<input '.( $value == 1 ? 'checked="checked"' : '' ).' type="checkbox" '.self::tag_properties( $atts ).'>';
        $output .= '<span>'.$text.'</span>';

        self::titleTag( $title, $output, $class );
        self::descriptionTag( $description, $output, $class );
        self::wrapper( $wrapper, $output, $wrapper_class );

        return $output; 
    }

    /**
     * Magic method for open/close tag
     */
    static function __callStatic( $method, $args ){
        $tagname = strtolower( $method );

        $atts = '';

        if( isset( $args[1] ) && is_array( $args[1] ) ){
            $atts = self::tag_properties( $args[1] );
        }

        $content = '';
        
        if( isset( $args[0] ) ){
            $content = $args[0];
        }

        return '<' .$tagname.$atts.'>' . $content . '</' .$tagname . '>';
    }

}
