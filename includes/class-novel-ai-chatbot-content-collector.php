<?php
/**
 * Handles content collection, chunking, and embedding for AI context.
 *
 * @link       https://novelnetware.com/
 * @since      1.2.0
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/includes
 */

class Novel_AI_Chatbot_Content_Collector {

    private $plugin_name;
    private $version;
    public  $ai_integration;
    private $embeddings_table_name;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->ai_integration = new Novel_AI_Chatbot_AI_Integration( $plugin_name, $version );

        global $wpdb;
        $this->embeddings_table_name = $wpdb->prefix . 'novel_ai_chatbot_embeddings';
    }

    /**
     * Registers AJAX hooks for content collection.
     */
    public function register_ajax_hooks() {
        add_action( 'wp_ajax_novel_ai_chatbot_check_sitemap', array( $this, 'ajax_check_sitemap' ) );
        add_action( 'wp_ajax_novel_ai_chatbot_collect_content', array( $this, 'ajax_collect_content' ) );
        add_action( 'wp_ajax_novel_ai_chatbot_clear_collection', array( $this, 'ajax_clear_collection' ) );
        add_action( 'wp_ajax_novel_ai_chatbot_get_collection_summary', array( $this, 'ajax_get_collection_summary' ) );
    }

    /**
     * AJAX: Checks if a sitemap.xml exists.
     */
    public function ajax_check_sitemap() {
        // (This function remains unchanged from your original code)
        check_ajax_referer( 'novel-ai-chatbot-admin-nonce', '_ajax_nonce' );
        $sitemap_url = isset( $_POST['sitemap_url'] ) ? esc_url_raw( wp_unslash( $_POST['sitemap_url'] ) ) : '';
        if ( ! $sitemap_url ) {
            wp_send_json_error( array( 'message' => __( 'سایت مپ خالی میباشد.', 'novel-ai-chatbot' ) ) );
        }
        $response = wp_remote_head( $sitemap_url, array( 'timeout' => 5, 'sslverify' => false ) );
        if ( is_wp_error( $response ) ) {
            wp_send_json_success( array( 'found' => false, 'message' => $response->get_error_message() ) );
        }
        $status_code = wp_remote_retrieve_response_code( $response );
        if ( 200 === $status_code ) {
            wp_send_json_success( array( 'found' => true ) );
        } else {
            wp_send_json_success( array( 'found' => false, 'status_code' => $status_code ) );
        }
    }

    /**
     * Splits text into smaller chunks for embedding.
     *
     * @param string $text The text to be chunked.
     * @param int $max_chunk_size The target maximum size of a chunk in words.
     * @return array An array of text chunks.
     */
    public function chunk_text( $text, $max_chunk_size = 200 ) {
        $text = str_replace( array( "\r\n", "\r", "\n" ), " ", $text ); // Normalize newlines
        $text = preg_replace( '/\s+/', ' ', $text ); // Collapse multiple spaces
        $sentences = preg_split( '/(?<=[.?!])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY );

        if ( empty( $sentences ) ) {
            return array();
        }

        $chunks = array();
        $current_chunk = '';

        foreach ( $sentences as $sentence ) {
            $current_chunk_word_count = str_word_count( $current_chunk );
            $sentence_word_count = str_word_count( $sentence );

            if ( $current_chunk_word_count + $sentence_word_count > $max_chunk_size ) {
                if ( ! empty( $current_chunk ) ) {
                    $chunks[] = trim( $current_chunk );
                }
                $current_chunk = $sentence;
            } else {
                $current_chunk .= ' ' . $sentence;
            }
        }
        if ( ! empty( $current_chunk ) ) {
            $chunks[] = trim( $current_chunk );
        }
        return $chunks;
    }

    /**
     * AJAX: Collects, chunks, embeds, and stores content from posts.
     */
    public function ajax_collect_content() {
        check_ajax_referer( 'novel-ai-chatbot-admin-nonce', '_ajax_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'دسترسی غیرمجاز.', 'novel-ai-chatbot' ) ] );
        }

        global $wpdb;
        $options = get_option( 'novel_ai_chatbot_options' );
        $selected_post_types = isset( $options['post_types'] ) ? (array) $options['post_types'] : [];
        
        $offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
        $batch_size = 2; // Process 2 posts per request to avoid Gemini API rate limits.

        $query_args = [
            'post_type'      => $selected_post_types,
            'post_status'    => 'publish',
            'posts_per_page' => $batch_size,
            'offset'         => $offset,
        ];
        
        $query = new WP_Query( $query_args );
        $total_posts = $query->found_posts;

        if ( ! $query->have_posts() ) {
            wp_send_json_success( [ 'finished' => true, 'message' => __( 'تمام محتوا ها پردازش شدند.', 'novel-ai-chatbot' ) ] );
            return;
        }

        $posts_to_process = $query->posts;
        foreach ( $posts_to_process as $post ) {
            // 1. Clean and chunk content
            $content = wp_strip_all_tags( apply_filters( 'the_content', $post->post_content ) );
            $chunks = $this->chunk_text( $content );

            if ( empty( $chunks ) ) {
                continue; // Skip posts with no content
            }

            // 2. Generate Embeddings for all chunks in one API call
            $embeddings = $this->ai_integration->generate_embedding( $chunks );

            if ( is_wp_error( $embeddings ) ) {
                // If API fails, stop and report error
                wp_send_json_error( [ 'message' => __( 'خطا:', 'novel-ai-chatbot' ) . ' ' . $embeddings->get_error_message() ] );
                return;
            }
            
            // 3. Store chunks and embeddings in the database
            for ( $i = 0; $i < count( $chunks ); $i++ ) {
                if ( ! isset( $embeddings[ $i ] ) ) continue; // Skip if embedding failed for a chunk

                $chunk_text = $chunks[ $i ];
                $embedding_vector = json_encode( $embeddings[ $i ] ); // Store as JSON string
                $chunk_hash = md5( $chunk_text );

                $wpdb->replace(
                    $this->embeddings_table_name,
                    [
                        'post_id'          => $post->ID,
                        'post_type'        => $post->post_type,
                        'chunk_hash'       => $chunk_hash,
                        'chunk_text'       => $chunk_text,
                        'embedding_vector' => $embedding_vector,
                    ],
                    [ '%d', '%s', '%s', '%s', '%s' ]
                );
            }
        }

        $next_offset = $offset + $query->post_count;
        $finished = ( $next_offset >= $total_posts );

        wp_send_json_success( [
            'processed_pages' => $next_offset,
            'total_pages'     => $total_posts,
            'next_offset'     => $next_offset,
            'finished'        => $finished,
            'message'         => __( 'جمع آوری اطلاعات موفقیت آمیز بود.', 'novel-ai-chatbot' ),
        ] );
    }

    /**
     * AJAX: Clears all collected embedding data.
     */
    public function ajax_clear_collection() {
        check_ajax_referer( 'novel-ai-chatbot-admin-nonce', '_ajax_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'دسترسی غیرمجاز.', 'novel-ai-chatbot' ) ] );
        }
        global $wpdb;
        $wpdb->query( "TRUNCATE TABLE {$this->embeddings_table_name}" );
        wp_send_json_success( [ 'message' => __( 'تمام قبلی حذف گردید.', 'novel-ai-chatbot' ) ] );
    }

    /**
     * AJAX: Gets a summary of the collected embeddings.
     */
    public function ajax_get_collection_summary() {
        check_ajax_referer( 'novel-ai-chatbot-admin-nonce', '_ajax_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'دسترسی غیرمجاز.', 'novel-ai-chatbot' ) ] );
        }
        global $wpdb;
        $total_collected = $wpdb->get_var( "SELECT COUNT(id) FROM {$this->embeddings_table_name}" );
        $latest_items_raw = $wpdb->get_results(
            "SELECT id, post_id, chunk_text, created_at FROM {$this->embeddings_table_name} ORDER BY created_at DESC LIMIT 10"
        );
        
        $latest_items = array_map(function($item) {
            return [
                'title'   => get_the_title($item->post_id),
                'url'     => get_permalink($item->post_id),
                'summary' => mb_substr($item->chunk_text, 0, 100) . '...', // Use chunk text as summary
                'date'    => $item->created_at,
                'type'    => get_post_type($item->post_id)
            ];
        }, $latest_items_raw);

        wp_send_json_success( [
            'total_collected' => $total_collected,
            'latest_items'    => $latest_items,
        ] );
    }
}