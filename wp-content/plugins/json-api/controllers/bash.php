<?php
/*
Controller name: Bash
Controller description: Booking Bash retrieval methods
*/
class JSON_API_Bash_Controller {
    //Striping tags
    function strip_tag($post){
        $post->excerpt = strip_tags($post->excerpt);
        $post->content = strip_tags($post->content);
    }
    //Returns all posts
    public function get_posts(){
        global $json_api;
        if ($json_api->query->id){
            $id = $json_api->query->id;
            $pics = get_post_meta($id, 'gallery', true);
            $feat_img = wp_get_attachment_url(get_post_thumbnail_id($id));
            $gallery[] = $feat_img;
            foreach($pics as $p){
                $pic = get_post($p);
                $gallery[] = $pic->guid;
            }
            $posts = array(
                'post' => new JSON_API_Post(get_post($id)),
                'gallery' => $gallery
            );
        }
        else{
            $posts = $json_api->introspector->get_posts(array(
                'post_type' => array('event', 'movie', 'club'),
                'orderby' => 'date',
                'order' => 'DESC',
                'posts_per_page' => -1
            ));
        }
        if (!empty($posts)){
            return $posts;
        }
        else{
            $json_api->error(array(
                'Code' => 204,
                'Message' => 'Nothing found.'
            ));
        }
    }
    //Returns post details for search_bar
    function search_bar(){
        global $json_api;
        $posts = $json_api->introspector->get_posts(array(
            'post_type' => array('event', 'movie', 'club'),
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => -1
        ));
        $search = array();
        foreach($posts as $p){
            $search[] = array(
                'id' => $p->id,
                'title' => $p->title,
                'type' => $p->type
            );
        }
        if (!empty($search)){
            return $search;
        }
        else{
            $json_api->error(array(
                'Code' => 204,
                'Message' => 'Nothing found.'
            ));
        }
    }
    //Returns posts for main slider
    public function homepage_slider(){
        global $wpdb, $json_api;
        $results = $wpdb->get_results("SELECT ID FROM wp_posts INNER JOIN wp_postmeta AS mt on (wp_posts.ID = mt.post_id) WHERE (mt.meta_key = 'homepage_slider' AND mt.meta_value = 'Yes') ORDER BY post_date DESC");
        if (!empty($results)){
            $posts = array();
            foreach($results as $r){
                $posts[] = new JSON_API_Post(get_post($r));
            }
            return $posts;
        }
        else{
            $json_api->error(array(
                'Code' => 204,
                'Message' => 'Nothing found.'
            ));
        }
    }
    //Returns filtered results for main pages filter
    public function filter_results(){
        global $json_api, $wpdb;
        if ($json_api->query->movies && $json_api->query->cinema && $json_api->query->date && $json_api->query->time){
            $search_term = $json_api->query->movie;
            if (strpos($search_term, '%20') != -1){
                $search_term = str_replace('%20', ' ', $search_term);
            }
            $q_cinema = $json_api->query->cinema;
            $q_date = $json_api->query->date;
            $q_time = $json_api->query->time;
            $search_results = array();
            $results = $wpdb->get_results("SELECT ID FROM wp_posts WHERE post_type = 'movie' AND post_status = 'publish' AND (LOCATE('{$search_term}', post_title) >     0)");
            if (!empty($results)){
                foreach($results as $r){
                    $cinemas = get_field('cinemas', $r);
                    foreach($cinemas as $c){
                        $cinema = $c['cinema_name'];
                        $date = $c['date'];
                        $time = $c['movie_timing'];
                        if (strtolower($cinema) == strtolower($q_cinema) && $time == $q_time && $date = $q_date){
                            $search_results[] = new JSON_API_Post(get_post($r));
                        }
                    }
                }
            }
        }
        elseif ($json_api->query->event && $json_api->query->venue && $json_api->query->date){
            $search_term = $json_api->query->event;
            if (strpos($search_term, '%20') != -1){
                $search_term = str_replace('%20', ' ', $search_term);
            }
            $q_venue = $json_api->query->venue;
            $q_date = $json_api->query->date;
            $q_date = explode('/', $q_date);
            $date = $q_date[2] . $q_date[0] . $q_date[1];
            $search_results = array();
            $results = $wpdb->get_results("SELECT ID FROM wp_posts INNER JOIN wp_postmeta AS mt1 ON (wp_posts.ID = mt1.post_id) INNER JOIN wp_postmeta AS mt2 ON (wp_posts.ID = mt2.post_id) WHERE post_type = 'event' AND post_status = 'publish' AND (LOCATE('{$search_term}', post_title) = 1) AND (mt1.meta_key = 'date' AND mt1.meta_value = '{$date}') AND (mt2.meta_key = 'is_activity' AND mt2.meta_value = 'No')");
            if (!empty($results)){
                foreach($results as $r){
                    $in_loc = get_field('map_location', $r);
                    $loc = $in_loc['address'];
                    if (strpos($loc, $q_venue) >= 0){
                        $search_results[] = new JSON_API_Post(get_post($r));
                    }
                }
            }
            if (!empty($search_results)){$ids = array();
                foreach($search_results as $r){
                    $ids[] = $r->id;
                }
                $map_data = array();
                foreach($search_results as $r){
                    $post = get_post($r->id);
                    $map_data[] = array(
                        'id' => $r->id,
                        'title' => $post->post_title,
                        'content' => $post->post_content,
                        'location' => get_post_meta($r->id, 'map_location', true)
                    );
                }
                $search_results = array($search_results, $ids, $map_data);
            }
        }
        elseif ($json_api->query->activity && $json_api->query->venue && $json_api->query->date){
            $search_term = $json_api->query->activity;
            if (strpos($search_term, '%20') != -1){
                $search_term = str_replace('%20', ' ', $search_term);
            }
            $q_venue = $json_api->query->venue;
            $q_date = $json_api->query->date;
            $q_date = explode('/', $q_date);
            $date = $q_date[2] . $q_date[0] . $q_date[1];
            $search_results = array();
            $results = $wpdb->get_results("SELECT ID FROM wp_posts INNER JOIN wp_postmeta AS mt ON (wp_posts.ID = mt.post_id) INNER JOIN wp_postmeta AS mt1 ON (wp_posts.ID = mt1.post_id) WHERE post_type='event' AND (LOCATE('{$search_term}', post_title) != 0) AND (mt.meta_key = 'location' AND mt.meta_value = '{$q_venue}') AND (mt1.meta_key = 'date' AND mt1.meta_value = '{$date}')");
            if (!empty($results)){
                foreach($results as $r){
                    $search_results[] = new JSON_API_Post(get_post($r));
                }
            }
            if (!empty($search_results)){$ids = array();
                foreach($search_results as $r){
                    $ids[] = $r->id;
                }
                $search_results = array($search_results, $ids);
            }
        }
        elseif ($json_api->query->dj && $json_api->query->venue && $json_api->query->genre){
            $search_term = $json_api->query->dj;
            if (strpos($search_term, '%20') != -1){
                $search_term = str_replace('%20', ' ', $search_term);
            }
            $q_venue = $json_api->query->venue;
            $genre = $json_api->query->genre;
            $search_results = array();
            $results = $wpdb->get_results("SELECT ID FROM wp_posts INNER JOIN wp_postmeta AS mt ON (wp_posts.ID = mt.post_id) INNER JOIN wp_postmeta AS mt1 ON (wp_posts.ID = mt1.post_id) INNER JOIN wp_postmeta AS mt2 ON (wp_posts.ID = mt2.post_id) WHERE post_type = 'club' AND post_status = 'publish' AND (LOCATE('{$search_term}', post_title) = 1) AND (mt.meta_key = 'location' AND mt.meta_value = '{$q_venue}') AND (mt1.meta_key = 'genre' AND mt1.meta_value = '{$genre}') AND (mt2.meta_key = 'is_dj' AND mt2.meta_value = 'Yes')");
            if (!empty($results)){
                foreach($results as $r){
                    $search_results[] = new JSON_API_Post(get_post($r));
                }
            }
            if (!empty($search_results)){
                $ids = array();
                foreach($search_results as $r){
                    $ids[] = $r->id;
                }
                $search_results = array($search_results, $ids);
            }
        }
        elseif ($json_api->query->facilities && $json_api->query->music && $json_api->query->type && $json_api->query->genre){
            $facilities = $json_api->query->facilities;
            $music = $json_api->query->music;
            $genre = $json_api->query->genre;
            $type = $json_api->query->type;
            $search_results = array();
            $results = $wpdb->get_results("SELECT ID FROM wp_posts INNER JOIN wp_postmeta AS mt ON (wp_posts.ID = mt.post_id) INNER JOIN wp_postmeta AS mt1 ON (wp_posts.ID = mt1.post_id) INNER JOIN wp_postmeta AS mt2 ON (wp_posts.ID = mt2.post_id) INNER JOIN wp_postmeta AS mt3 ON (wp_posts.ID = mt3.post_id) INNER JOIN wp_postmeta AS mt4 ON (wp_posts.ID = mt4.post_id) WHERE post_type = 'club' AND post_status = 'publish' AND (mt.meta_key = 'facilities' AND mt.meta_value LIKE '{$facilities}') AND (mt1.meta_key = 'music' AND mt1.meta_value = '{$music}') AND (mt2.meta_key = 'is_dj' AND mt2.meta_value = 'No') AND (mt3.meta_key = 'genre' AND mt3.meta_value = '{$genre}') AND (mt4.meta_key = 'type' AND mt4.meta_value = '{$type}')");
            if (!empty($results)){
                foreach($results as $r){
                    $search_results[] = new JSON_API_Post(get_post($r));
                }
            }
            if(!empty($search_results)){
                $ids = array();
                foreach($search_results as $r){
                    $ids[] = $r->id;
                }
                $search_results = array($search_results, $ids);
            }
        }
        else {
            $json_api->error(array(
                'Code' => 400,
                'Message' => 'Please include a search parameter.'
            ));
        }
        if (!empty($search_results)){
            return $search_results;
        }
        else{
            $json_api->error(array(
                'Code' => 204,
                'Message' => 'Nothing found.'
            ));
        }
    }
    //Returns featured content
    public function featured_content(){
        global $json_api;
        $url = parse_url($_SERVER['REQUEST_URI']);
        $query = wp_parse_args($url['query']);
        if (!empty($query)){
            $type = $json_api->query->type;
            //Returns featured movies
            if ($type == 'movie'){
                $posts = $json_api->introspector->get_posts(array(
                    'post_type' => 'movie',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'meta_key' => 'featured',
                    'meta_value' => 'Yes',
                    'posts_per_page' => $json_api->query->count ? $json_api->query->count : 100,
                ));
            }
            //Returns featured events
            elseif ($type == 'event'){
                $filter = $json_api->query->filter;
                if ($filter){
                    if ($filter == 'today'){
                        $in_posts = $json_api->introspector->get_posts(array(
                            'post_type' => 'event',
                            'orderby' => 'date',
                            'order' => 'DESC',
                            'posts_per_page' => 100,
                            'meta_query' => array(
                                array(
                                    'key' => 'date',
                                    'value' => date('Ymd'),
                                    'compare' => '='
                                ),
                                array(
                                    'key' => 'is_activity',
                                    'value' => 'No'
                                )
                            )
                        ));
                    }
                    elseif($filter == 'week'){
                        $week_str =date('Ymd', strtotime('monday this week'));
                        $week_end = date('Ymd', strtotime('sunday this week'));
                        $in_posts = $json_api->introspector->get_posts(array(
                            'post_type' => 'event',
                            'orderby' => 'date',
                            'order' => 'DESC',
                            'posts_per_page' => 100,
                            'meta_query' => array(
                                array(
                                    'key' => 'is_activity',
                                    'value' => 'No'
                                ),
                                array(
                                    'key' => 'date',
                                    'value' => array($week_str, $week_end),
                                    'compare' => 'BETWEEN',
                                    'type' => 'DATE'
                                )
                            )
                        ));
                    }
                    elseif($filter == 'month'){
                        $mon_str =date('Ymd', strtotime('first day of this month'));
                        $mon_end = date('Ymd', strtotime('last day of this month'));
                        $in_posts = $json_api->introspector->get_posts(array(
                            'post_type' => 'event',
                            'orderby' => 'date',
                            'order' => 'DESC',
                            'posts_per_page' => 100,
                            'meta_query' => array(
                                array(
                                    'key' => 'is_activity',
                                    'value' => 'No'
                                ),
                                array(
                                    'key' => 'date',
                                    'value' => array($mon_str, $mon_end),
                                    'compare' => 'BETWEEN',
                                    'type' => 'DATE'
                                )
                            )
                        ));
                    }
                    elseif($filter == 'future'){
                        $mon_end = date('Ymd', strtotime('last day of this month'));
                        $in_posts = $json_api->introspector->get_posts(array(
                            'post_type' => 'event',
                            'orderby' => 'date',
                            'order' => 'DESC',
                            'posts_per_page' => 100,
                            'meta_query' => array(
                                array(
                                    'key' => 'is_activity',
                                    'value' => 'No'
                                ),
                                array(
                                    'key' => 'date',
                                    'value' => $mon_end,
                                    'compare' => '>'
                                )
                            )
                        ));
                    }
                    else{
                        $json_api->error(array(
                            'Code' => 406,
                            'Message' => 'Please enter a correct parameter.'
                        ));
                    }
                    if (!empty($in_posts)){
                        $ids = array();
                        foreach($in_posts as $p){
                            $ids[] = $p->id;
                        }
                        $posts = array($in_posts, $ids);
                    }
                }
                else{
                    $posts = $json_api->introspector->get_posts(array(
                        'post_type' => 'event',
                        'orderby' => 'date',
                        'order' => 'DESC',
                        'posts_per_page' => $json_api->query->count ? $json_api->query->count : 100,
                        'meta_query' => array(
                            array(
                                'key' => 'featured',
                                'value' => 'Yes'
                            ),
                            array(
                                'key' => 'is_activity',
                                'value' => 'No'
                            )
                        )
                    ));
                }
            }
            //Returns featured activities
            elseif ($type == 'activity'){
                $in_posts = $json_api->introspector->get_posts(array(
                    'post_type' => 'event',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'featured',
                            'value' => 'Yes'
                        ),
                        array(
                            'key' => 'is_activity',
                            'value' => 'Yes'
                        )
                    )
                ));
                if (!empty($in_posts)){
                    $ids = array();
                    foreach($in_posts as $p){
                        $ids[] = $p->id;
                    }
                    $posts = array($in_posts, $ids);
                }
            }
            //Returns featured clubs
            elseif ($type == 'club'){
                $posts = $json_api->introspector->get_posts(array(
                    'post_type' => 'club',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'meta_query' => array(
                        array(
                            'key' => 'featured',
                            'value' => 'Yes'
                        ),
                        array(
                            'key' => 'is_dj',
                            'value' => 'No'
                        )

                    )
                ));
            }
            //Returns featured DJs
            elseif ($type == 'dj'){
                $posts = $json_api->introspector->get_posts(array(
                    'post_type' => 'club',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'featured',
                            'value' => 'Yes'
                        ),
                        array(
                            'key' => 'is_dj',
                            'value' => 'Yes'
                        )
                    )
                ));
            }
            elseif ($type == 'nightlife'){
                $in_posts = $json_api->introspector->get_posts(array(
                    'post_type' => 'club',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'featured',
                            'value' => 'Yes'
                        )
                    )
                ));
                if (!empty($in_posts)){
                    $ids = array();
                    foreach($in_posts as $p){
                        $ids[] = $p->id;
                    }
                    $posts = array($in_posts, $ids);
                }
            }
            else{
                $json_api->error(array(
                    'Code' => 406,
                    'Message' => 'Please enter a correct parameter.'
                ));
            }
        }
        //Returns all featured content
        else{
            $posts = $json_api->introspector->get_posts(array(
                'post_type' => array('event', 'movie', 'club'),
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_key' => 'featured',
                'meta_value' => 'Yes'
            ));
        }
        if (!empty($posts)){
            return $posts;
        }
        else{
            $json_api->error(array(
                'Code' => 204,
                'Message' => 'Nothing found.'
            ));
        }
    }
    //Returns all events
    public function events(){
        global $json_api;
        $url = parse_url($_SERVER['REQUEST_URI']);
        $query = wp_parse_args($url['query']);
        //Returns events on the basis of date range
        if (!empty($query)){
            if ($json_api->query->date){
                $filter = $json_api->query->date;
                $filter = explode('-', $filter);
                $in_events = get_posts(array(
                    'post_type' => 'event',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'meta_key' => 'is_activity',
                    'meta_value' => 'No'
                ));
                foreach($in_events as $event){
                    $in_date = get_post_meta($event->ID, 'date', true);
                    $date = substr($in_date, 4, 2) . '/' . substr($in_date, 6, 2) . '/' . substr($in_date, 0, 4);
                    $date = strtotime($date);
                    $str_date = strtotime($filter[0]);
                    $end_date = strtotime($filter[1]);
                    if (empty($str_date) || empty($end_date)){
                        $json_api->error(array(
                            'Code' => 406,
                            'Message' => "'Please enter dates in correct format i.e \'MONTH/DATE/YEAR\'"
                        ));
                    }
                    if ($date >= $str_date && $date <= $end_date){
                        $events[] = array(
                            'post' => new JSON_API_Post($event)
                        );
                    }
                }
            }
            //Returns events scheduled for a specific month
            elseif ($json_api->query->month){
                $mon = $json_api->query->month;
                $in_events = get_posts(array(
                    'post_type' => 'event',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'meta_key' => 'is_activity',
                    'meta_value' => 'No'
                ));
                $events = array();
                if (!empty($in_events)){
                    foreach($in_events as $event){
                        $date = get_post_meta($event->ID, 'date', true);
                        $month = (int)substr($date, 4, 2);
                        $year = (int)substr($date, 0, 4);
                        $curr_year = date('Y');
                        if ($month >= $mon && $year = $curr_year){
                            $events[] = array(
                                'post' => new JSON_API_Post($event)
                            );
                        }
                    }
                }
            }
            //Returns  events under queried category
            elseif ($json_api->query->category){
                $cat = $json_api->query->category;
                $events = $json_api->introspector->get_posts(array(
                    'post_type' => 'event',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'meta_query' => array(
                        array(
                            'key' => 'is_activity',
                            'value' => 'No'
                        ),
                        array(
                            'key' => 'category',
                            'value' => $cat,
                            'compare' => '='
                        )
                    )
                ));
                if (!empty($events)){
                    $ids = array();
                    foreach($events as $e){
                        $ids[] = $e->id;
                    }
                    $map_data = array();
                    foreach($events as $r){
                        $post = get_post($r->id);
                        $map_data[] = array(
                            'id' => $r->id,
                            'title' => $post->post_title,
                            'content' => $post->post_content,
                            'location' => get_post_meta($r->id, 'map_location', true)
                        );
                    }
                    $events = array($events, $ids, $map_data);
                }
            }
            //Returns events of a specific artist
            elseif ($json_api->query->artist){
                $art = $json_api->query->artist;
                if (strpos($art,'-')){
                    $art = str_replace('-', ' ', $art);
                }
                $events = $json_api->introspector->get_posts(array(
                    'post_type' => 'event',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'meta_query' => array(
                        array(
                            'key' => 'is_activity',
                            'value' => 'No'
                        ),
                        array(
                            'key' => 'artist',
                            'value' => $art,
                            'compare' => '='
                        )
                    )
                ));
            }
            elseif ($json_api->query->id){
                $id = $json_api->query->id;
                $pics = get_post_meta($id, 'gallery', true);
                $feat_img = wp_get_attachment_url(get_post_thumbnail_id($id));
                $gallery[] = $feat_img;
                foreach($pics as $p){
                    $pic = get_post($p);
                    $gallery[] = $pic->guid;
                }
                $post = new JSON_API_Post(get_post($id));
                if (get_post_type($id) == 'event'){
                    $events = array(
                        'post' => $post,
                        'gallery' => $gallery
                    );
                }
            }
            else{
                $json_api->error(array(
                    'Code' => 406,
                    'Message' => 'Please enter a correct parameter.'
                ));
            }
        }
        //Returns all events
        else{
            $in_events = $json_api->introspector->get_posts(array(
                'post_type' =>'event',
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_key' => 'is_activity',
                'meta_value' => 'No',
                'posts_per_page' => 100
            ));
            $ids = array();
            foreach($in_events as $e){
                $ids[] = $e->id;
            }
            $map_data = array();
            foreach($in_events as $r){
                $post = get_post($r->id);
                $map_data[] = array(
                    'id' => $r->id,
                    'title' => $post->post_title,
                    'content' => $post->post_content,
                    'image' => wp_get_attachment_url(get_post_thumbnail_id($r->id)),
                    'location' => get_post_meta($r->id, 'map_location', true)
                );
            }
            $events = array($in_events, $ids, $map_data);
        }
        if (!empty($events)){
            return $events;
        }
        else{
            $json_api->error(array(
                'Code' => 204,
                'Message' => 'Nothing found.'
            ));
        }
    }
    //Returns all clubs
    public function clubs(){
        global $json_api;
        if ($json_api->query->id){
            $id = $json_api->query->id;
            $pics = get_post_meta($id, 'gallery', true);
            $feat_img = wp_get_attachment_url(get_post_thumbnail_id($id));
            $gallery[] = $feat_img;
            foreach($pics as $p){
                $pic = get_post($p);
                $gallery[] = $pic->guid;
            }
            $post = new JSON_API_Post(get_post($id));
            //$feat_img = $post->attachments['url'];
            if (get_post_type($id) == 'club'){
                $clubs = array(
                    'post' => $post,
                    'gallery' => $gallery
                );
            }
        }
        else{
            $in_clubs = $json_api->introspector->get_posts(array(
                'post_type' => 'club',
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_key' => 'is_dj',
                'meta_value' => 'No',
                'posts_per_page' => 100
            ));
            $ids = array();
            foreach($in_clubs as $c){
                $ids[] = $c->id;
            }
            $clubs = array($in_clubs, $ids);
        }
        if (!empty($clubs)){
            return $clubs;
        }
        else{
            $json_api->error(array(
                'Code' => 204,
                'Message' => 'Nothing found.'
            ));
        }
    }
    //Returns all DJs
    public function djs(){
        global $json_api, $wpdb;
        $url = parse_url($_SERVER['REQUEST_URI']);
        $query = wp_parse_args($url['query']);
        //Returns all DJs of queried genre
        if (!empty($query)){
            if ($json_api->query->genre){
                $genre = $json_api->query->genre;
                $djs = $json_api->introspector->get_posts(array(
                    'post_type' => 'club',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'meta_query' => array(
                        array(
                            'key' => 'is_dj',
                            'value' => 'Yes'
                        ),
                        array(
                            'key' => 'genre',
                            'value' => $genre,
                            'compare' => '='
                        )
                    )
                ));
                if (!empty($djs)){
                    $ids = array();
                    foreach($djs as $d){
                        $ids[] = $d->id;
                    }
                    $djs = array($djs, $ids);
                }
            }
            //Returns all DJs of queried name
            elseif ($json_api->query->name){
                $name = $json_api->query->name;
                $results = $wpdb->get_results("SELECT * FROM wp_posts INNER JOIN wp_postmeta AS mt ON (wp_posts.ID = mt.post_id) WHERE post_type = 'club' AND (mt.meta_key = 'is_dj' AND mt.meta_value = 'Yes') AND post_title = '{$name}'");
                $djs = array();
                if (!empty($results)){
                    foreach($results as $r){
                        $djs[] = array(
                            'post' => new JSON_API_Post($r)
                        );
                    }
                }
            }
            elseif ($json_api->query->id){
                $id = $json_api->query->id;
                $pics = get_post_meta($id, 'gallery', true);
                $feat_img = wp_get_attachment_url(get_post_thumbnail_id($id));
                $gallery[] = $feat_img;
                foreach($pics as $p){
                    $pic = get_post($p);
                    $gallery[] = $pic->guid;
                }
                $post = new JSON_API_Post(get_post($id));
                //$feat_img = $post->attachments['url'];
                if (get_post_type($id) == 'club'){
                    $djs = array(
                        'post' => $post,
                        'gallery' => $gallery
                    );
                }
            }
            else{
                $json_api->error(array(
                    'Code' => 406,
                    'Message' => 'Please enter a correct parameter.'
                ));
            }
        }
        //Returns all DJs
        else{
            $in_djs = $json_api->introspector->get_posts(array(
                'post_type' => 'club',
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_key' => 'is_dj',
                'meta_value' => 'Yes',
                'posts_per_page' => 100
            ));
            $ids = array();
            foreach($in_djs as $d){
                $ids[] = $d->id;
            }
            $djs = array($in_djs, $ids);
        }
        if (!empty($djs)){
            return $djs;
        }
        else{
            $json_api->error(array(
                'Code' => 204,
                'Message' => 'Nothing found.'
            ));
        }
    }
    //Returns all activities
    public function activities(){
        global $json_api;
        $url = parse_url($_SERVER['REQUEST_URI']);
        $query = wp_parse_args($url['query']);
        //Returns activities under queried category
        if (!empty($query)){
            if ($json_api->query->category){
                $cat = $json_api->query->category;
                $activities = $json_api->introspector->get_posts(array(
                    'post_type' => 'event',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'meta_query' => array(
                        array(
                            'key' => 'is_activity',
                            'value' => 'Yes'
                        ),
                        array(
                            'key' => 'category',
                            'value' => $cat,
                            'compare' => '='
                        )
                    )
                ));
                if (!empty($activities)){
                    $ids = array();
                    foreach($activities  as $a){
                        $ids[] = $a->id;
                    }
                    $activities = array($activities, $ids);
                }
            }
            //Returns activities of queried sponsor/company
            elseif ($json_api->query->sponsor){
                $spon = $json_api->query->sponsor;
                $activities = $json_api->introspector->get_posts(array(
                    'post_type' => 'event',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'meta_query' => array(
                        array(
                            'key' => 'is_activity',
                            'value' => 'Yes'
                        ),
                        array(
                            'key' => 'sponsor',
                            'value' => $spon,
                            'compare' => '='
                        )
                    )
                ));
            }
            elseif ($json_api->query->id){
                $id = $json_api->query->id;
                $pics = get_post_meta($id, 'gallery', true);
                $feat_img = wp_get_attachment_url(get_post_thumbnail_id($id));
                $gallery[] = $feat_img;
                foreach($pics as $p){
                    $pic = get_post($p);
                    $gallery[] = $pic->guid;
                }
                $post = new JSON_API_Post(get_post($id));
                //$feat_img = $post->attachments['url'];
                if (get_post_type($id) == 'event'){
                    $activities = array(
                        'post' => $post,
                        'gallery' => $gallery
                    );
                }
            }
            else{
                $json_api->error(array(
                    'Code' => 406,
                    'Message' => 'Please enter a correct parameter.'
                ));
            }
        }
        //Returns all activities
        else{
            $in_activities = $json_api->introspector->get_posts(array(
                'post_type' => 'event',
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_key' => 'is_activity',
                'meta_value' => 'Yes',
                'posts_per_page' => 100
            ));
            $ids = array();
            foreach($in_activities as $a){
                $ids[] = $a->id;
            }
            $activities = array($in_activities, $ids);
        }
        if (!empty($activities)){
            return $activities;
        }
        else{
            $json_api->error(array(
                'Code' => 204,
                'Message' => 'Nothing found.'
            ));
        }
    }
    //Returns all movies
    public function movies(){
        global $json_api;
        $url = parse_url($_SERVER['REQUEST_URI']);
        $query = wp_parse_args($url['query']);
        //Returns activities under queried category
        if (!empty($query)){
            if ($json_api->query->filter){
                $filter = $json_api->query->filter;
                if ($filter == 'latest'){
                    $movies = $json_api->introspector->get_posts(array(
                        'post_type' => 'movie',
                        'orderby' => 'meta_value_num',
                        'meta_key' => 'release_date',
                        'meta_value' => date('Ymd'),
                        'meta_compare' => '<'
                    ));
                }
                elseif ($filter == 'coming-soon'){
                    $movies = $json_api->introspector->get_posts(array(
                        'post_type' => 'movie',
                        'meta_key' => 'release_date',
                        'meta_value' => date('Ymd'),
                        'meta_compare' => '>'

                    ));
                }
                elseif ($filter == 'top-rated'){
                    $movies = $json_api->introspector->get_posts(array(
                        'post_type' => 'movie',
                        'orderby' => 'meta_value_num',
                        'meta_key' => 'rating'

                    ));
                }
                elseif($filter == 'genre' && $json_api->query->genre){
                    global $wpdb;
                    $genre = $json_api->query->genre;
                    $results = $wpdb->get_results("SELECT ID FROM wp_posts INNER JOIN wp_postmeta AS mt ON (wp_posts.ID = mt.post_id) WHERE (mt.meta_key = 'genre' AND mt.meta_value LIKE '%{$genre}%')", ARRAY_A);
                    foreach($results as $r){
                        $movies[] = new JSON_API_Post(get_post($r['ID']));
                    }
                }
                else{
                    $json_api->error(array(
                        'Code' => 406,
                        'Message' => 'Please enter a correct parameter.'
                    ));
                }
            }
            elseif($json_api->query->id){
                $movie = get_post($json_api->query->id);
                $cast = get_post_meta($movie->ID, 'movie_cast', true);
                $cinemas = get_field('cinemas', $movie->ID);
                foreach($cinemas as $c){
                    $cinema_names[] = $c['cinema_name'];
                }
                $cinema_names = array_unique($cinema_names);
                $movie_cast = array();
                for ($i = 0; $i < $cast; $i++){
                    $dob = get_post_meta($movie->ID, "movie_cast_".$i."_cast_dob", true);
                    $dob = jdmonthname(substr($dob, 4, 2), 0) . ' ' . (substr($dob, 6, 2)) . ', ' . (substr($dob, 0, 4));
                    $movie_cast[] = array(
                        'name' => get_post_meta($movie->ID, "movie_cast_".$i."_cast_name", true),
                        'DOB' => $dob,
                        'title' => get_post_meta($movie->ID, "movie_cast_".$i."_cast_title", true),
                        'height' => get_post_meta($movie->ID, "movie_cast_".$i."_cast_height", true),
                        'picture' => wp_get_attachment_url(get_post_meta($movie->ID, "movie_cast_".$i."_picture", true)),
                    );
                }
                if (!empty($movie) && $movie->post_type == 'movie' && $movie->post_status == 'publish'){
                    $movies = array(
                        'post' => new JSON_API_Post($movie),
                        'cast' => $movie_cast,
                        'cinemas' => $cinema_names,
                        'timings' => $cinemas
                    );
                }
            }
            else{
                $json_api->error(array(
                    'Code' => 406,
                    'Message' => 'Please enter a correct parameter.'
                ));
            }
        }
        else{
            $movies = $json_api->introspector->get_posts(array(
                'post_type' => 'movie',
                'orderby' => 'date',
                'order' => 'DESC',
                'posts_per_page' => 1000
            ));
        }
        if (!empty($movies)){
            return $movies;
        }
        else{
            $json_api->error(array(
                'Code' => 204,
                'Message' => 'Nothing found.'
            ));
        }
    }
    //Returns booking bash video
    function bbash_video(){
        global $json_api;
        $video = get_post_meta(19, 'bbash_video', true);
        if (!empty($video) && $video != ''){
            if (strpos($video, 'youtube')){
                $video = str_replace('watch?v=', 'embed/', $video);
                $video_url = array(
                    'video_url' => $video . '?autoplay=1'
                );
            }
            if (strpos($video, 'vimeo')){
                $player = str_replace('vimeo.com', 'player.vimeo.com/video', $video);
                $video = explode('/', $video);
                $video_id = $video[4];
                $url = $player . $video_id;
                $video_url = array(
                    'video_url' => $url . '?autoplay=1'
                );
            }
            return $video_url;
        }
        else{
            $json_api->error(array(
                'Code' => 204,
                'Message' => 'Sorry, the video does not exist.'
            ));
        }
    }
    //Check if user already exists
    function check_username() {
        global $json_api;
        if ($json_api->query->user_email) {
            $email = $json_api->query->user_email;
            if (email_exists($email)) {
                $json_api->error('User already exists.');
            }
            else {
                return true;
            }
        }
        else {
            echo 'Please enter correct parameters.';
        }
    }
    //Registers a user
    function user_register(){
        global $json_api;
        $user_name = $_REQUEST['username'];
        $email_id = $_REQUEST['email'];
        $password = $_REQUEST['password'];
        if ($user_name && $email_id && $password){
            $user_id = wp_insert_user(array(
                'user_login' => $user_name,
                'user_email' => $email_id,
                'user_pass' => $password,
                'role' => 'subscriber'
            ));
            if ( ! is_wp_error( $user_id ) ) {
                return array(
                    'Message' => 'User created successfully',
                    'Status' => 'ok',
                    'User_id' => $user_id,
                    'username' => get_userdata($user_id)->display_name
                );
            }
            else{
                return array('Status' => 'Error',
                    'message' => 'User not created');
            }
        }
        else{
            $json_api->error('Please enter correct parameters.');
        }
    }
    //Logs in a user
    function user_login(){
        global $json_api;
        $user_name = $_REQUEST['username'];
        $password = $_REQUEST['password'];
        if ($user_name && $password){
            $creds = array();
            $creds['user_login'] = $user_name;
            $creds['user_password'] = $password;
            $creds['remember'] = true;
            $check = wp_signon( $creds, true );
            //$check = wp_authenticate_username_password( NULL, $user_name, $password);
            if ( is_wp_error($check) ){
                return array('Status' => 'error',
                    'Message' => 'Incorrect user name or password.');
            }
            else{
                return array(
                    'Status' => 'ok',
                    'Message' => 'User logged in successfully.',
                    'User_id' => $check->ID,
                    'username' => get_userdata($check->ID)->display_name
                );
            }
        }
    }
    //Returns session id for facebook login
    function facebook_login(){
        return $_SESSION['fb_access_token'];
    }
    //Returns movie trailers for movies main
    function movie_trailers(){
        global $json_api;
        $trailers = array();
        $trailer1 = get_post_meta(186, 'trailer_1', true);
        $trailer2 = get_post_meta(186, 'trailer_2', true);
        $trailer3 = get_post_meta(186, 'trailer_3', true);
        $trailer4 = get_post_meta(186, 'trailer_4', true);
        $trailer5 = get_post_meta(186, 'trailer_5', true);
        if (!empty($trailer1)){
            $trailer1 = str_replace('watch?v=', 'embed/', $trailer1);
            array_push($trailers, $trailer1);
        }
        if (!empty($trailer2)){
            $trailer2 = str_replace('watch?v=', 'embed/', $trailer2);
            array_push($trailers, $trailer2);
        }
        if (!empty($trailer3)){
            $trailer3 = str_replace('watch?v=', 'embed/', $trailer3);
            array_push($trailers, $trailer3);
        }
        if (!empty($trailer4)){
            $trailer4 = str_replace('watch?v=', 'embed/', $trailer4);
            array_push($trailers, $trailer4);
        }
        if (!empty($trailer5)){
            $trailer5 = str_replace('watch?v=', 'embed/', $trailer5);
            array_push($trailers, $trailer5);
        }
        if (!empty($trailers)){
            return $trailers;
        }
        else{
            $json_api->error(array(
                'Code' => 204,
                'Message' => 'Nothing found.'
            ));
        }
    }
    //Returns blog posts
    function blog_posts(){
        global $json_api;
        if ($json_api->query->id){
            $in_posts = $json_api->introspector->get_posts(array(
                'orderby' => 'date',
                'order' => 'ASC',
                'post__not_in' => array(186, 19)
            ));
            $in_ids = array();
            foreach($in_posts as $p){
                $in_ids[] = $p->id;
            }
            $id = $json_api->query->id;
            $post = get_post($id);
            if (!empty($post)){
                $c_id = $post->ID;
                $c_id = array_search($c_id, $in_ids);
                $p_id = $c_id -1;
                $n_id = $c_id +1;
                $ids = array(($p_id != -1) ? $in_ids[$p_id] : 0, ($n_id < sizeof($in_ids)) ? $in_ids[$n_id] : 0 );
                $posts = array (
                    'post' => new JSON_API_Post($post),
                    'ids' => $ids
                );
            }
            else{
                $json_api->error(array(
                    'Code' => 204,
                    'Message' => 'Post not found.'
                ));
            }
        }
        else{
            $posts = $json_api->introspector->get_posts(array(
                'orderby' => 'date',
                'order' => 'ASC',
                'post__not_in' => array(186, 19)
            ));
        }
        if (!empty($posts)){
            return $posts;
        }
        else{
            $json_api->error(array(
                'Code' => 204,
                'Message' => 'Nothing found.'
            ));
        }
    }
    //Subscribes a user for newsletter
    function news_letter(){
        global $wpdb;
        $email = $_REQUEST['email'];
        if (strpos($email, '@')){
            $wpdb->insert("newsletter", array('email' => $email));
            return array('Status' =>'ok');
        }
        else{
            return array('Status' => 'error');
        }
    }
    //Updates post likes
    function like(){
        $post_id = $_REQUEST['post_id'];
        $user_id = $_REQUEST['user_id'];
        $likes = get_post_meta($post_id, 'like_users', true);
        $liked_posts = get_user_meta($user_id, 'liked', true);
        //Adds post to the user
        if (!empty($liked_posts)){
            if (!is_array($liked_posts)){
                $liked_posts = array($liked_posts);
            }
            $key = in_array($post_id, $liked_posts);
            if ($key == false){
                $liked_posts[] = $post_id;
            }
            update_user_meta($user_id, 'liked', $liked_posts);
        }
        else{
            update_user_meta($user_id, 'liked', $post_id);
        }
        //Adds user to the post
        if (!empty($likes)){
            if (!is_array($likes)){
                $likes = array($likes);
            }
            $key = in_array($user_id, $likes);
            if ($key == false){
                $likes[] = $user_id;
            }
            update_post_meta($post_id, 'like_users', $likes);
        }
        else{
            update_post_meta($post_id, 'like_users', $user_id);
        }
        return 'Success';
    }
    //Removes post likes
    function unlike(){
        $post_id = $_REQUEST['post_id'];
        $user_id = $_REQUEST['user_id'];
        $like_users = get_post_meta($post_id, 'like_users', true);
        $liked = get_user_meta($user_id, 'liked', true);
        //Removes post from user
        if (!empty($liked)){
            if (is_array($liked) && sizeof($liked) != 1){
                $key1 = array_search($post_id, $liked);
                if ($key1 >= 0){
                    unset($liked[$key1]);
                    update_user_meta($user_id, 'liked', $liked);
                }
            }
            else{
                delete_user_meta($user_id, 'liked');
            }
        }
        //Removes user from post
        if (!empty($like_users)){
            if (is_array($like_users) && sizeof($liked) != 1){
                $key =array_search($user_id, $like_users);
                if ($key >= 0){
                    unset($like_users[$key]);
                    update_post_meta($post_id, 'like_users', $like_users);
                }
            }
            else{
                delete_post_meta($post_id, 'like_users');
            }
        }
        return $key1;
    }
    //Returns user liked content
    function liked_content(){
        global $json_api;
        /*$posts = $json_api->introspector->get_posts(array(
            'post_type' => array('event', 'movie', 'club'),
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => -1
        ));
        foreach($posts as $p){
            delete_post_meta($p->id, 'like_users');
        }*/
        $user_id = $json_api->query->user_id;
        $content = get_user_meta($user_id, 'liked');
        if (!is_array($content)){
            $content = array($content);
        }
        $posts = array();
        foreach($content[0] as $c){
            $posts[] = new JSON_API_Post(get_post($c));
        }
        return $posts;
    }
}
