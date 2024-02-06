<?php
function post_list_style() {
	wp_register_style( 'post_list-css', get_template_directory_uri() . '/css/post_list.css' );
}
add_action( 'wp_enqueue_scripts', 'post_list_style' );
/*【出力カスタマイズ】ショートコードに値を指定して表示結果をコントロール */
function shortcode_post_list($atts) { // 変数の定義
	extract(shortcode_atts(array( // 引数の値を取得
	  'num' => '3', // 表示件数の値、引数の指定がなければ指定の値を格納
	  'post_type' => 'post', // 投稿タイプの値、引数の指定がなければ指定の値を格納
	  'slug' => '', // カテゴリーid、初期値は0
	  'exclusion_id' => '',
	  'day_period_start' => '',
	  'day_period_end' => '',
	  'order' => '',
	  'orderby'	=> 'post_date',
	  'sort_by_ranking_style' => false
	), $atts));
	global $post; // グローバル宣言
	wp_enqueue_style( 'post_list-css' );
	// 除外IDの配列化
	if( isset( $exclusion_id )) {
		// カンマで分割
		$exclusion_id_Array = explode( ',', $exclusion_id );
	}

	$args = array( // クエリの準備
		'meta_key'		 => 'cf_popular_posts',
		'posts_per_page' => $num, // 表示件数の指定
		'post_type'      => $post_type, // 投稿タイプの指定
		'post_status'    => 'publish', // 投稿ステータスの指定
		'category_name'  => $slug, // カテゴリー
		'date_query' => array(
			'before' => $day_period_start,
			'after' => $day_period_end,
			'inclusive' => true //afterとbeforeに指定した日を含めるかどうか
		),
		'order'			 => $order,
		'orderby'		 => $orderby,
		'exclude' 		 => $exclusion_id_Array //除外ID
	);
	$posts_array = get_posts($args); // クエリを基にした投稿情報を取得
	$html = '<div class="custom-rank-post-list">';

	foreach($posts_array as $post): // ループの開始
		setup_postdata($post); // 投稿のセットアップ
		$html .= '<div class="rank-post-list">';
		$html .= '<div class="rank-post-flex">';
		if ( has_post_thumbnail( $post->ID ) ) {
			$html .= '<a class="post-thumb-link-wrap" href="'.get_permalink().'" >';
			$html .= '<div class="post-thumb" style="background-image: url('.get_the_post_thumbnail_url( get_the_ID(), 'medium' ).');">';
			$html .= '</div>';
			$html .= '</a>';
		} else {
			$html .= '<a class="post-thumb-link-wrap" href="'.get_permalink().'" >';
			$html .= '<div class="post-thumb" style="background-image: url();">';
			$html .= '</div>';
			$html .= '</a>';
		}

		$html .= '<div class="rank-post-inner col2">';
		$html .= '<a href="'.get_permalink().'" >';
		$html .= '<h3>'.wp_trim_words(get_the_title(), 29, '…').'</h3>'; // リンク付きタイトルを表示
		$html .= '</a>';
		$html .= '<a href="'.get_permalink().'" >';
		$html .= '<p>'.mb_substr( get_the_excerpt(), 0, 90 ).'[...]</p>';
		$html .= '</a>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
	endforeach; // ループの終了

	$html.='</div>';
	wp_reset_postdata(); // 投稿のリセット
	return $html;
  }
  add_shortcode('post_list', 'shortcode_post_list');


  function my_custom_popular_posts($post_id) {
	$count_key = 'cf_popular_posts';
	$count = get_post_meta($post_id, $count_key, true);
	if ($count == '') {
	  $count = 0;
	  delete_post_meta($post_id, $count_key);
	  add_post_meta($post_id, $count_key, '0');
	} else {
	  $count++;
	  update_post_meta($post_id, $count_key, $count);
	}
  }
  function my_custom_track_posts($post_id) {
	if (!is_single()) return;
	if (empty($post_id)) {
	  global $post;
	  $post_id = $post->ID;
	}
	my_custom_popular_posts($post_id);
  }
  add_action('wp_head', 'my_custom_track_posts');
	?>
