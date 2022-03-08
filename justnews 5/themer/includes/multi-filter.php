<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Multi_Filter{
    function __construct(){
        $this->baseurl = add_query_arg(array());
        add_action( 'init', array($this, 'create_taxonomy') );
        add_action( 'category_before_list', array($this, 'render') );
        add_action( 'pre_get_posts', array($this, 'parse_filter') );
    }

    function parse_filter($query) {
        if ($query->is_main_query() && $query->is_category && $query->tax_query->queries) {
            $tax_query = array();
            if(isset($_POST['attr']) && $_POST['attr']){ // ajax翻页兼容
                $attrs = explode('+', $_POST['attr']);
                $tax_query = $query->query['tax_query'];
                if($attrs){
                    foreach ($attrs as $a){
                        $terms = explode(',', $a);
                        $tax = array(
                            'taxonomy' => 'attr',
                            'field' => 'slug',
                            'operator' => 'IN',
                            'include_children' => 1
                        );
                        $tax['terms'] = $terms;
                        $tax_query[] = $tax;
                    }
                }
                $query->query['attr'] = $_POST['attr'];
            }else{
                foreach ($query->tax_query->queries as $tax){
                    if($tax['taxonomy'] === 'attr' && $tax['terms'] && is_array($tax['terms']) && count($tax['terms']) === 1){
                        $terms = explode(',', $tax['terms'][0]);
                        $tax['terms'] = $terms;
                        $tax_query[] = $tax;
                    }
                }
            }
            if(!empty($tax_query)) {
                $query->set( 'attr', '' );
                $query->set( 'tax_query', $tax_query );
            }
            if(isset($_GET['order']) && $_GET['order']){
                switch ($_GET['order']){
                    case 'title':
                        $order = 'ASC';
                        $orderby = 'title date';
                        break;
                    case 'views':
                        $order = 'DESC';
                        $orderby = 'meta_value_num';
                        $meta_key = 'views';
                        break;
                    case 'likes':
                        $order = 'DESC';
                        $orderby = 'meta_value_num';
                        $meta_key = 'wpcom_likes';
                        break;
                    case 'favorites':
                        $order = 'DESC';
                        $orderby = 'meta_value_num';
                        $meta_key = 'wpcom_favorites';
                        break;
                    case 'comments':
                        $order = 'DESC';
                        $orderby = 'comment_count date';
                        break;
                }
                if(isset($orderby)) $query->set( 'orderby', $orderby );
                if(isset($order)) $query->set( 'order', $order );
                if(isset($meta_key)) $query->set( 'meta_key', $meta_key );
            }
        }
    }

    function create_taxonomy(){
        $labels = array(
            'name' => '筛选属性',
            'singular_name' => '多重筛选属性',
            'search_items' =>  '搜索属性',
            'all_items' => '所有属性',
            'menu_name' => '筛选属性',
            'edit_item' => '编辑属性',
            'update_item' => '更新属性',
            'add_new_item' => '添加属性',
            'new_item_name' => '新属性值',
            'not_found' => '暂无属性'
        );

        register_taxonomy('attr', array('post'), array(
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'publicly_queryable' => true,
            'public' => false
        ));
    }

    function get_data(){
        global $options, $wp_query;
        if(!empty($this->data)) return $this->data;
        $object_id = $wp_query->queried_object_id;
        $data = array();
        if($object_id && isset($options['filter_item_id']) && is_array($options['filter_item_id']) && $options['filter_item_id']){
            $filter = get_term_meta( $object_id, 'wpcom_filter_tool', true );
            if($filter && ($index = array_search($filter, $options['filter_item_id'])) !== false && $options['filter_item_label'][$index]){
                foreach ($options['filter_item_label'][$index] as $i => $label){
                    $data[$i] = array();
                    $data[$i]['label'] = $label;
                    $data[$i]['count'] = isset($options['filter_item_count']) && isset($options['filter_item_count'][$index]) ? $options['filter_item_count'][$index] : 1;
                    $data[$i]['type'] = $options['filter_item_type'][$index][$i];
                    if($data[$i]['type'] == '0'){
                        $data[$i]['items'] = $options['filter_item_cats'][$index][$i];
                    }else if($data[$i]['type'] == '1'){
                        $data[$i]['items'] = $options['filter_item_attrs'][$index][$i];
                    }else{
                        $data[$i]['items'] = '';
                    }
                }
            }
        }
        $this->data = $data;
        return $data;
    }

    function term_classes($term){
        global $wp_query;
        $classes = 'multi-filter-li';
        if($term->taxonomy === 'category' && ($term->term_id == $wp_query->queried_object_id || term_is_ancestor_of( $term->term_id, $wp_query->queried_object_id, $term->taxonomy )) ){
            $classes .= ' multi-filter-current';
        }else if($term->taxonomy === 'attr' && $this->has_term($term->slug)){
            $classes .= ' multi-filter-current';
        }
        return $classes;
    }

    function term_url($term){
        $url = $this->baseurl;
        if($term->taxonomy === 'category'){
            $url = get_term_link($term->term_id, $term->taxonomy);
        }else if($term->taxonomy){
            $data = $this->get_data();
            $attr = '';
            foreach ($data as $item){
                if($item['type']=='1'){
                    $_attr = '';
                    $terms = get_terms( array(
                        'taxonomy' => $term->taxonomy,
                        'hide_empty' => false,
                        'orderby' => 'include',
                        'include' => $item['items']
                    ) );
                    foreach ($terms as $tax){
                        if($tax->term_id == $term->term_id && $this->has_term($tax->slug)){
                        }else if($tax->term_id == $term->term_id || $this->has_term($tax->slug)){
                            $_attr .= ($_attr ? ',' : '') . $tax->slug;
                        }
                    }
                    if($_attr) $attr .= ($attr ? '+' : '') . $_attr;
                }
            }

            $url = add_query_arg($term->taxonomy, $attr ?: false);
        }
        return $url;
    }

    function has_term($slug){
        global $wp_query;
        $queries = $wp_query->tax_query->queries;
        foreach ($queries as $tax){
            if($tax['taxonomy'] === 'attr' && is_array($tax['terms']) && (in_array($slug, $tax['terms']) || in_array(urldecode($slug), $tax['terms']))){
                return true;
            }
        }
        return false;
    }

    public function render(){
        global $options;
        $data = $this->get_data();
        if(empty($data)) return false;
        ?>
        <section class="multi-filter">
            <?php foreach ($data as $item){
                $type = $item['type']=='1' ? 'attr' : ($item['type']?'order':'cat');
                ?>
                <div class="multi-filter-item multi-filter-<?php echo $type;?>">
                    <h4 class="multi-filter-title"><?php echo $item['label']?></h4>
                    <ul class="multi-filter-ul">
                        <?php if($item['type']=='2'){
                            $order = array(
                                '' => _x('Default', 'multi-filter', 'wpcom'),
                                'title' => _x('Title', 'multi-filter', 'wpcom'),
                                'views' => _x('Views', 'multi-filter', 'wpcom'),
                                'comments' => _x('Comments', 'multi-filter', 'wpcom'),
                                'likes' => _x('Likes', 'multi-filter', 'wpcom'),
                                'favorites' => _x('Favorites', 'multi-filter', 'wpcom')
                            );
                            if(!function_exists('the_views')) unset($order['views']);
                            if(isset($options['comments_open']) && !$options['comments_open']) unset($order['comments']);
                            if(!function_exists('wpcom_like_it')) unset($order['likes']);
                            if(!function_exists('wpcom_heart_it')) unset($order['favorites']);

                            foreach ($order as $k => $o){
                                $url = add_query_arg('order', $k ?: false, $this->baseurl);
                                $classes = 'multi-filter-li';
                                if((isset($_GET['order']) && $_GET['order'] === $k) || (!isset($_GET['order']) && !$k)){
                                    $classes .= ' multi-filter-current';
                                }
                                echo '<li class="'.$classes.'"><a href="'.esc_url($url).'">'.$o.'</a></li>';
                            }
                            ?>
                        <?php }else{
                            $terms = get_terms( array(
                                'taxonomy' => $item['type']=='1' ? 'attr' : 'category',
                                'hide_empty' => false,
                                'orderby' => 'include',
                                'include' => $item['items']
                            ) );
                            if($terms){
                                foreach ($terms as $term){
                                    $classes = $this->term_classes($term);
                                    $url = $this->term_url($term);
                                    ?>
                                    <li class="<?php echo $classes;?>">
                                        <a href="<?php echo esc_url($url);?>"><?php echo $term->name;?></a><?php if($item['count']){?> (<?php echo $term->count;?>)<?php } ?>
                                    </li>
                                <?php }
                            }
                        } ?>
                    </ul>
                    <a href="#" class="multi-filter-more"><?php WPCOM::icon('arrow-down');?></a>
                </div>
            <?php } ?>
        </section>
    <?php }
}