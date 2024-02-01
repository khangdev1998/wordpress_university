<?php

// Khai báo hàm pageBanner với tham số $args là một mảng với giá trị mặc định là NULL.
function pageBanner($args = NULL) {
  
  // Kiểm tra nếu 'title' trong mảng $args không được cung cấp, thì sẽ lấy tiêu đề của bài viết hiện tại.
  if (!$args['title']) {
    $args['title'] = get_the_title();
  }

  // Kiểm tra nếu 'subtitle' trong mảng $args không được cung cấp, thì sẽ lấy giá trị từ custom field 'page_banner_subtitle'.
  if (!$args['subtitle']) {
    $args['subtitle'] = get_field('page_banner_subtitle');
  }

  // Kiểm tra nếu 'photo' trong mảng $args không được cung cấp. Nếu custom field 'page_banner_background_image' tồn tại và không phải là trang lưu trữ hoặc trang chủ, sử dụng ảnh từ custom field này, còn không sẽ sử dụng ảnh mặc định.
  if (!$args['photo']) {
    if (get_field('page_banner_background_image') AND !is_archive() AND !is_home() ) {
      $args['photo'] = get_field('page_banner_background_image')['sizes']['pageBanner'];
    } else {
      $args['photo'] = get_theme_file_uri('/images/ocean.jpg');
    }
  }

  // HTML cho banner, sử dụng dữ liệu từ mảng $args.
  ?>
  <div class="page-banner">
    <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo']; ?>);"></div>
    <div class="page-banner__content container container--narrow">
      <h1 class="page-banner__title"><?php echo $args['title'] ?></h1>
      <div class="page-banner__intro">
        <p><?php echo $args['subtitle']; ?></p>
      </div>
    </div>  
  </div>
<?php }

// Hàm để thêm các tệp JavaScript và CSS vào theme.
function university_files() {
  // Đăng ký và thêm file JavaScript chính.
  wp_enqueue_script('main-university-js', get_theme_file_uri('/build/index.js'), array('jquery'), '1.0', true);
  
  // Thêm Google Fonts vào theme.
  wp_enqueue_style('custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
  
  // Thêm Font Awesome vào theme.
  wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
  
  // Thêm các tệp CSS chính vào theme.
  wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
  wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));
}

// Thêm hàm university_files vào hook wp_enqueue_scripts để nó chạy khi WordPress enqueue scripts.
add_action('wp_enqueue_scripts', 'university_files');

// Hàm để thêm các tính năng cho theme.
function university_features() {
  // Thêm hỗ trợ để WordPress quản lý thẻ tiêu đề của trang web.
  add_theme_support('title-tag');
  
  // Thêm hỗ trợ cho hình ảnh đại diện của bài viết (post thumbnails).
  add_theme_support('post-thumbnails');
  
  // Thêm kích thước hình ảnh tùy chỉnh.
  add_image_size('professorLandscape', 400, 260, true);
  add_image_size('professorPortrait', 480, 650, true);
  add_image_size('pageBanner', 1500, 350, true);
}

// Thêm hàm university_features vào hook after_setup_theme.
add_action('after_setup_theme', 'university_features');

// Hàm để tùy chỉnh các truy vấn WP_Query.
function university_adjust_queries($query) {
  // Kiểm tra nếu không phải là truy vấn trong admin, và là truy vấn chính cho post type 'program'.
  if (!is_admin() AND is_post_type_archive('program') AND $query->is_main_query()) {
    // Sắp xếp bài viết theo tiêu đề và theo thứ tự tăng dần.
    $query->set('orderby', 'title');
    $query->set('order', 'ASC');
    // Hiển thị tất cả bài viết.
    $query->set('posts_per_page', -1);
  }

  // Tương tự như trên nhưng dành cho post type 'event'.
  if (!is_admin() AND is_post_type_archive('event') AND $query->is_main_query()) {
    $today = date('Ymd');
    $query->set('meta_key', 'event_date');
    $query->set('orderby', 'meta_value_num');
    $query->set('order', 'ASC');
    // Chỉ hiển thị các sự kiện diễn ra từ ngày hiện tại trở đi.
    $query->set('meta_query', array(
              array(
                'key' => 'event_date',
                'compare' => '>=',
                'value' => $today,
                'type' => 'numeric'
              )
            ));
  }
}

// Thêm hàm university_adjust_queries vào hook pre_get_posts.
add_action('pre_get_posts', 'university_adjust_queries');

// Hàm để thiết lập API key cho Google Map sử dụng trong Advanced Custom Fields.
function universityMapKey($api) {
  // Thiết lập API key của bạn.
  $api['key'] = 'yourKeyGoesHere';
  return $api;
}

// Thêm hàm universityMapKey vào filter acf/fields/google_map/api.
add_filter('acf/fields/google_map/api', 'universityMapKey');
