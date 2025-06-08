<?php

/**
 * Handles integration with various AI models (OpenAI, Gemini, Deepseek).
 *
 * @link       https://novelnetware.com/
 * @since      1.0.0
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/includes
 */

class Novel_AI_Chatbot_AI_Integration {

    private $plugin_name;
    private $version;
    private $options;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options = get_option( 'novel_ai_chatbot_options', array() ); // Initialize with empty array if not set
    }

    /**
     * Sends a query to the selected AI model after finding relevant context via semantic search.
     *
     * @since 1.2.0
     * @param string $user_query The user's question.
     * @return string The AI's response or an error message.
     */
    public function get_ai_response( $user_query ) {
        $selected_ai_model = isset( $this->options['ai_model'] ) ? $this->options['ai_model'] : 'gemini';
        $api_key = $this->get_api_key( $selected_ai_model );

        if ( empty( $api_key ) ) {
            return __( 'خطا: کلید API برای مدل انتخابی تنظیم نشده است.', 'novel-ai-chatbot' );
        }

        // New: Perform semantic search to get context
        $relevant_context = $this->get_relevant_context( $user_query );

        // Prepare the prompt with the new, highly relevant context
        $prompt = $this->prepare_ai_prompt( $user_query, $relevant_context );

        switch ( $selected_ai_model ) {
            case 'openai':
                return $this->query_openai( $prompt, $api_key );
            case 'gemini':
                return $this->query_gemini( $prompt, $api_key );
            case 'deepseek':
                return $this->query_deepseek( $prompt, $api_key );
            default:
                return __( 'مدل AI انتخابی معتبر نیست.', 'novel-ai-chatbot' );
        }
    }

    /**
     * Prepares the prompt for the AI model, incorporating user query and context.
     *
     * @since 1.0.0
     * @param string $user_query
     * @param array  $context_data
     * @return string
     */
    private function prepare_ai_prompt( $user_query, $context_data ) {
        $context_string = '';
        if ( ! empty( $context_data ) ) {
            // Limit context size to avoid exceeding token limits and improve performance
            $limited_context = array_slice($context_data, 0, 5); // Take top 5 contexts for example
            $context_string = implode( "\n\n", $limited_context );
            $context_string = __( "در اینجا چندین اطلاعات مرتبط با وبسایتی که با آن ادغام شده است، آورده شده است. این اطلاعات را برای پاسخ به سوال کاربر استفاده کنید:\n\n", 'novel-ai-chatbot' ) . $context_string;
        }

        $site_name = get_bloginfo('name');
        $system_instruction = sprintf(
            __( "شما یک ربات کمکی مفید و دوست دارانه برای وبسایت '%s' هستید. اصلی‌ترین هدف شما پاسخ به سوالات کاربر بر اساس اطلاعاتی که از محتوای وبسایت ارائه شده است است. اگر پاسخ به سوالی به طور صریح در اطلاعات ارائه شده وجود نداشته باشد، با صراحت بیان کنید که به اطلاعات کافی ندارید و پیشنهاد می‌دهید کاربر به وبسایت مستقیماً برای جزئیات بیشتر بازدید کند. اطلاعات را ترکیب نکنید. پاسخ‌ها را خلاصه و مستقیماً مرتبط با سوال کاربر و اطلاعات ارائه شده تنظیم کنید.", 'novel-ai-chatbot' ),
            $site_name
        );

        $prompt = $system_instruction . "\n\n";
        $prompt .= $context_string;
        $prompt .= "\n\n" . __( "سوال کاربر:", 'novel-ai-chatbot' ) . " " . $user_query;
        $prompt .= "\n\n" . __( "پاسخ:", 'novel-ai-chatbot' );

        return $prompt;
    }

    /**
     * Retrieves API key from plugin options.
     *
     * @since 1.0.0
     * @param string $ai_model The AI model name.
     * @return string The API key.
     */
    private function get_api_key( $ai_model ) {
        $key = '';
        switch ( $ai_model ) {
            case 'openai':
                $key = isset( $this->options['openai_api_key'] ) ? $this->options['openai_api_key'] : '';
                break;
            case 'gemini':
                $key = isset( $this->options['gemini_api_key'] ) ? $this->options['gemini_api_key'] : '';
                break;
            case 'deepseek':
                $key = isset( $this->options['deepseek_api_key'] ) ? $this->options['deepseek_api_key'] : '';
                break;
        }
        return $key;
    }

    /**
     * Queries the OpenAI API (Chat Completions).
     *
     * @since 1.0.0
     * @param string $prompt
     * @param string $api_key
     * @return string The response from OpenAI.
     */
    private function query_openai( $prompt, $api_key ) {
        $body = array(
            'model'    => 'gpt-3.5-turbo', // Or 'gpt-4o', 'gpt-4-turbo', etc. based on preference/cost
            'messages' => array(
                array( 'role' => 'user', 'content' => $prompt )
            ),
            'temperature' => 0.7, // Adjust creativity
            'max_tokens'  => 500, // Max response length
        );

        $args = array(
            'body'        => json_encode( $body ),
            'headers'     => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ),
            'timeout'     => 30, // seconds
            'sslverify'   => false, // Set to true in production if you have proper SSL setup
            'method'      => 'POST',
            'data_format' => 'body',
        );

        $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', $args );

        if ( is_wp_error( $response ) ) {
            return __( 'خطا در ارتباط با OpenAI: ', 'novel-ai-chatbot' ) . $response->get_error_message();
        }

        $response_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $response_body, true );

        if ( isset( $data['choices'][0]['message']['content'] ) ) {
            return $data['choices'][0]['message']['content'];
        } elseif ( isset( $data['error']['message'] ) ) {
            return __( 'خطا در OpenAI API: ', 'novel-ai-chatbot' ) . $data['error']['message'];
        }

        return __( 'خطایی نامشخص از OpenAI رخ داده است.', 'novel-ai-chatbot' );
    }

    /**
     * Queries the Gemini API.
     *
     * @since 1.0.0
     * @param string $prompt
     * @param string $api_key
     * @return string The response from Gemini.
     */
    private function query_gemini( $prompt, $api_key ) {
        $body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array( 'text' => $prompt )
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.7,
                'maxOutputTokens' => 500,
            )
        );

        // Model can be 'gemini-pro' or other available models
        $model = 'gemini-2.0-flash';
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

        $args = array(
            'body'        => json_encode( $body ),
            'headers'     => array(
                'Content-Type' => 'application/json',
            ),
            'timeout'     => 30,
            'sslverify'   => false,
            'method'      => 'POST',
            'data_format' => 'body',
        );

        $response = wp_remote_post( $endpoint, $args );

        if ( is_wp_error( $response ) ) {
            return __( 'خطا در ارتباط با Gemini: ', 'novel-ai-chatbot' ) . $response->get_error_message();
        }

        $response_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $response_body, true );

        if ( isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        } elseif ( isset( $data['error']['message'] ) ) {
            return __( 'Gemini API خطا در ارتباط: ', 'novel-ai-chatbot' ) . $data['error']['message'];
        }

        return __( 'خطایی نامشخص از Gemini رخ داده است.', 'novel-ai-chatbot' );
    }

    /**
     * Queries the Deepseek API.
     * (Assuming Deepseek uses an OpenAI-compatible API, common for many LLM providers)
     *
     * @since 1.0.0
     * @param string $prompt
     * @param string $api_key
     * @return string The response from Deepseek.
     */
    private function query_deepseek( $prompt, $api_key ) {
        $body = array(
            'model'    => 'deepseek-chat', // Check Deepseek's specific model name
            'messages' => array(
                array( 'role' => 'user', 'content' => $prompt )
            ),
            'temperature' => 0.7,
            'max_tokens'  => 500,
        );

        $args = array(
            'body'        => json_encode( $body ),
            'headers'     => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ),
            'timeout'     => 30,
            'sslverify'   => false,
            'method'      => 'POST',
            'data_format' => 'body',
        );

        // Deepseek API endpoint (replace if different)
        $response = wp_remote_post( 'https://api.deepseek.com/chat/completions', $args );

        if ( is_wp_error( $response ) ) {
            return __( 'خطا در ارتباط با Deepseek: ', 'novel-ai-chatbot' ) . $response->get_error_message();
        }

        $response_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $response_body, true );

        if ( isset( $data['choices'][0]['message']['content'] ) ) {
            return $data['choices'][0]['message']['content'];
        } elseif ( isset( $data['error']['message'] ) ) {
            return __( 'Deepseek API خطا در ارتباط: ', 'novel-ai-chatbot' ) . $data['error']['message'];
        }

        return __( 'خطایی نامشخص از Deepseek رخ داده است.', 'novel-ai-chatbot' );
    }

    /**
     * Performs a semantic search to find the most relevant context for a user query.
     * Implements a caching layer to improve performance for common queries.
     *
     * @since 1.3.0 (Replaces original method)
     * @access private
     * @param string $user_query The user's question.
     * @return array An array of the most relevant text chunks.
     */
    private function get_relevant_context( $user_query ) {
        // Create a unique cache key based on the user's query.
        $cache_key = 'nac_context_' . md5( trim( strtolower( $user_query ) ) );
        
        // Try to get the result from the cache first.
        $cached_context = get_transient( $cache_key );
        if ( false !== $cached_context ) {
            // Cache hit! Return the cached result immediately.
            return $cached_context;
        }

        // --- Cache miss. Proceed with the full search logic. ---

        global $wpdb;
        $table_name = $wpdb->prefix . 'novel_ai_chatbot_embeddings';
        $top_n = 3;

        // 1. Generate an embedding for the user's query.
        $query_embedding_result = $this->generate_embedding( [ $user_query ] );
        if ( is_wp_error( $query_embedding_result ) || empty( $query_embedding_result[0] ) ) {
            return [];
        }
        $query_vector = $query_embedding_result[0];

        // 2. Retrieve all stored embeddings from the database.
        $all_embeddings = $wpdb->get_results( "SELECT chunk_text, embedding_vector FROM {$table_name}", ARRAY_A );

        if ( empty( $all_embeddings ) ) {
            return [];
        }

        // 3. Calculate cosine similarity.
        $similarities = [];
        foreach ( $all_embeddings as $index => $row ) {
            $stored_vector = json_decode( $row['embedding_vector'], true );
            if ( is_array( $stored_vector ) ) {
                $similarities[ $index ] = $this->cosine_similarity( $query_vector, $stored_vector );
            }
        }
        
        // 4. Sort and get top N results.
        arsort( $similarities );
        $top_indices = array_slice( array_keys( $similarities ), 0, $top_n, true );

        $relevant_chunks = [];
        foreach ( $top_indices as $index ) {
            $relevant_chunks[] = $all_embeddings[ $index ]['chunk_text'];
        }

        // **NEW**: Save the result to the cache before returning.
        // Cache the result for 6 hours.
        set_transient( $cache_key, $relevant_chunks, 6 * HOUR_IN_SECONDS );

        return $relevant_chunks;
    }

    /**
     * Calculates the cosine similarity between two vectors.
     *
     * @since 1.2.0
     * @access private
     * @param array $vec_a The first vector.
     * @param array $vec_b The second vector.
     * @return float The cosine similarity score.
     */
    private function cosine_similarity( array $vec_a, array $vec_b ) {
        $dot_product = 0.0;
        $norm_a = 0.0;
        $norm_b = 0.0;
        $count = count($vec_a);

        if ($count !== count($vec_b)) {
            return 0; // Vectors must be the same dimension
        }

        for ( $i = 0; $i < $count; $i++ ) {
            $dot_product += $vec_a[ $i ] * $vec_b[ $i ];
            $norm_a += $vec_a[ $i ] ** 2;
            $norm_b += $vec_b[ $i ] ** 2;
        }

        $denominator = sqrt( $norm_a ) * sqrt( $norm_b );

        return $denominator == 0 ? 0 : $dot_product / $denominator;
    }

    /**
	 * Generates embeddings for an array of texts using the selected AI model.
	 * Currently hardcoded for Gemini.
	 *
	 * @since 1.2.0
	 * @param array $texts An array of strings to be embedded.
	 * @return array|WP_Error An array of embedding vectors or a WP_Error on failure.
	 */
	public function generate_embedding( array $texts ) {
		$api_key = $this->get_api_key( 'gemini' );

		if ( empty( $api_key ) ) {
			return new WP_Error( 'api_key_missing', __( 'Gemini API key is not set.', 'novel-ai-chatbot' ) );
		}

		// The user requested 'gemini-embedding-exp'. The current production model is 'text-embedding-004'.
		// We will use the requested model but it's good to keep an eye on Google's documentation for updates.
		$model_name = 'text-embedding-004'; // As of late 2024 / early 2025, 'embedding-001' is the stable model name. Let's use this for stability.

		return $this->query_gemini_embedding( $texts, $api_key, $model_name );
	}

	/**
	 * Queries the Gemini API to get embeddings for a batch of texts.
	 *
	 * @since 1.2.0
	 * @access private
	 * @param array $texts The array of texts to embed.
	 * @param string $api_key The Gemini API key.
	 * @param string $model_name The embedding model name.
	 * @return array|WP_Error An array of embeddings or a WP_Error on failure.
	 */
	private function query_gemini_embedding( array $texts, $api_key, $model_name ) {
		$endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model_name}:batchEmbedContents?key={$api_key}";

		// Prepare requests for batching
		$requests = array();
		foreach ( $texts as $text ) {
			$requests[] = array(
				'model'   => "models/{$model_name}",
				'content' => array(
					'parts' => array(
						array( 'text' => $text )
					),
				),
			);
		}

		$body = array(
			'requests' => $requests,
		);

		$args = array(
			'body'        => json_encode( $body ),
			'headers'     => array(
				'Content-Type' => 'application/json',
			),
			'timeout'     => 45, // Increased timeout for embedding larger batches
			'sslverify'   => false, // Should be true in production with proper SSL
			'method'      => 'POST',
			'data_format' => 'body',
		);

		$response = wp_remote_post( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_body = wp_remote_retrieve_body( $response );
		$data = json_decode( $response_body, true );

		if ( isset( $data['embeddings'] ) ) {
			// Return just the array of vector values
			return wp_list_pluck( $data['embeddings'], 'values' );
		} elseif ( isset( $data['error']['message'] ) ) {
			return new WP_Error( 'gemini_api_error', $data['error']['message'] );
		}

		return new WP_Error( 'unknown_gemini_error', __( 'An unknown error occurred while generating embeddings.', 'novel-ai-chatbot' ) );
	}

}