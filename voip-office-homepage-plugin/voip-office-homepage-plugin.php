<?php
/**
 * Plugin Name: VoIP Office Homepage Builder
 * Description: Converts a static VoIP Office homepage into a shortcode-driven WordPress plugin with dashboard-managed options.
 * Version: 1.1.0
 * Version: 1.0.0
 * Author: VoIP Office
 */

if (!defined('ABSPATH')) {
    exit;
}

class VoipOfficeHomepagePlugin {
    const OPTION_KEY = 'voip_office_homepage_options';

    public function __construct() {
        add_action('admin_menu', array($this, 'register_admin_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_shortcode('voip_office_homepage', array($this, 'render_shortcode'));
    }

    public static function activate() {
        $plugin = new self();
        if (!get_option(self::OPTION_KEY)) {
            add_option(self::OPTION_KEY, $plugin->get_default_options());
        }
    }

    public function register_admin_page() {
        add_menu_page(
            'VoIP Homepage Builder',
            'VoIP Homepage',
            'manage_options',
            'voip-office-homepage',
            array($this, 'render_admin_page'),
            'dashicons-admin-customizer',
            60
        );
    }

    public function register_settings() {
        register_setting(
            'voip_office_homepage_group',
            self::OPTION_KEY,
            array($this, 'sanitize_options')
        );
    }

    public function sanitize_options($input) {
        $defaults = $this->get_default_options();
        $output = wp_parse_args((array) $input, $defaults);

        $text_fields = array(
            'hero_badge_1', 'hero_badge_2', 'hero_sub_heading', 'hero_heading_before',
            'hero_heading_highlight', 'hero_description', 'hero_cta_1_text', 'hero_cta_1_url',
            'hero_cta_2_text', 'hero_cta_2_url', 'hero_image_url', 'hero_image_alt',
            'hero_metric_1_value', 'hero_metric_1_label', 'hero_metric_2_value', 'hero_metric_2_label',
            'hero_metric_3_value', 'hero_metric_3_label', 'hero_metric_4_value', 'hero_metric_4_label',
            'color_dark_primary', 'color_accent', 'color_light_gray', 'color_white',
            'hero_desktop_heading_size', 'hero_tablet_heading_size', 'hero_mobile_heading_size',
            'hero_desktop_padding', 'hero_tablet_padding', 'hero_mobile_padding',
            'desktop_breakpoint', 'tablet_breakpoint', 'mobile_breakpoint',
            'global_section_padding_desktop', 'global_section_padding_tablet', 'global_section_padding_mobile',
            'global_base_font_desktop', 'global_base_font_tablet', 'global_base_font_mobile'
            'hero_desktop_padding', 'hero_tablet_padding', 'hero_mobile_padding'
        );

        foreach ($text_fields as $field) {
            $output[$field] = sanitize_text_field($output[$field]);
        }

        $output['hero_stack_breakpoint'] = absint($output['hero_stack_breakpoint']);
        $output['hero_stack_breakpoint'] = $output['hero_stack_breakpoint'] > 0 ? $output['hero_stack_breakpoint'] : 992;

        $output['desktop_breakpoint'] = absint($output['desktop_breakpoint']);
        $output['desktop_breakpoint'] = $output['desktop_breakpoint'] > 0 ? $output['desktop_breakpoint'] : 1200;

        $output['tablet_breakpoint'] = absint($output['tablet_breakpoint']);
        $output['tablet_breakpoint'] = $output['tablet_breakpoint'] > 0 ? $output['tablet_breakpoint'] : 992;

        $output['mobile_breakpoint'] = absint($output['mobile_breakpoint']);
        $output['mobile_breakpoint'] = $output['mobile_breakpoint'] > 0 ? $output['mobile_breakpoint'] : 768;

        $output['hero_force_stacked_mobile'] = !empty($input['hero_force_stacked_mobile']) ? 1 : 0;
        $output['remove_button_underline'] = !empty($input['remove_button_underline']) ? 1 : 0;

        $output['image_overrides'] = array();
        if (!empty($input['image_overrides']) && is_array($input['image_overrides'])) {
            foreach ($input['image_overrides'] as $idx => $url) {
                $index = absint($idx);
                $output['image_overrides'][$index] = esc_url_raw($url);
            }
        }
        $output['hero_force_stacked_mobile'] = !empty($input['hero_force_stacked_mobile']) ? 1 : 0;

        if (current_user_can('unfiltered_html')) {
            $output['template_html'] = (string) $output['template_html'];
            $output['custom_css'] = (string) $output['custom_css'];
            $output['custom_js'] = (string) $output['custom_js'];
            $output['responsive_css_tablet'] = (string) $output['responsive_css_tablet'];
            $output['responsive_css_mobile'] = (string) $output['responsive_css_mobile'];
        } else {
            $output['template_html'] = wp_kses_post($output['template_html']);
            $output['custom_css'] = sanitize_textarea_field($output['custom_css']);
            $output['custom_js'] = sanitize_textarea_field($output['custom_js']);
            $output['responsive_css_tablet'] = sanitize_textarea_field($output['responsive_css_tablet']);
            $output['responsive_css_mobile'] = sanitize_textarea_field($output['responsive_css_mobile']);
        }

        return $output;
    }

    public function render_admin_page() {
        $options = wp_parse_args(get_option(self::OPTION_KEY, array()), $this->get_default_options());
        $template_images = $this->extract_template_images($options['template_html']);
        ?>
        <div class="wrap">
            <h1>VoIP Office Homepage Builder</h1>
            <p>Use shortcode <code>[voip_office_homepage]</code> on any page to display this homepage.</p>
            <form method="post" action="options.php">
                <?php settings_fields('voip_office_homepage_group'); ?>

                <h2>Hero Content</h2>
                <table class="form-table" role="presentation">
                    <?php $this->render_input_row($options, 'hero_badge_1', 'Hero Badge 1'); ?>
                    <?php $this->render_input_row($options, 'hero_badge_2', 'Hero Badge 2'); ?>
                    <?php $this->render_input_row($options, 'hero_sub_heading', 'Hero Sub Heading'); ?>
                    <?php $this->render_input_row($options, 'hero_heading_before', 'Hero Heading (Before Highlight)'); ?>
                    <?php $this->render_input_row($options, 'hero_heading_highlight', 'Hero Heading Highlight'); ?>
                    <?php $this->render_textarea_row($options, 'hero_description', 'Hero Description', 3); ?>
                    <?php $this->render_input_row($options, 'hero_cta_1_text', 'CTA 1 Text'); ?>
                    <?php $this->render_input_row($options, 'hero_cta_1_url', 'CTA 1 URL'); ?>
                    <?php $this->render_input_row($options, 'hero_cta_2_text', 'CTA 2 Text'); ?>
                    <?php $this->render_input_row($options, 'hero_cta_2_url', 'CTA 2 URL'); ?>
                    <?php $this->render_input_row($options, 'hero_image_url', 'Hero Image URL'); ?>
                    <?php $this->render_input_row($options, 'hero_image_alt', 'Hero Image Alt'); ?>
                </table>

                <h2>Hero Metrics</h2>
                <table class="form-table" role="presentation">
                    <?php $this->render_input_row($options, 'hero_metric_1_value', 'Metric 1 Value'); ?>
                    <?php $this->render_input_row($options, 'hero_metric_1_label', 'Metric 1 Label'); ?>
                    <?php $this->render_input_row($options, 'hero_metric_2_value', 'Metric 2 Value'); ?>
                    <?php $this->render_input_row($options, 'hero_metric_2_label', 'Metric 2 Label'); ?>
                    <?php $this->render_input_row($options, 'hero_metric_3_value', 'Metric 3 Value'); ?>
                    <?php $this->render_input_row($options, 'hero_metric_3_label', 'Metric 3 Label'); ?>
                    <?php $this->render_input_row($options, 'hero_metric_4_value', 'Metric 4 Value'); ?>
                    <?php $this->render_input_row($options, 'hero_metric_4_label', 'Metric 4 Label'); ?>
                </table>

                <h2>All Images on Homepage</h2>
                <p>Every detected &lt;img&gt; on your homepage can be overridden here without editing HTML.</p>
                <table class="form-table" role="presentation">
                    <?php foreach ($template_images as $image): ?>
                        <tr>
                            <th scope="row"><?php echo esc_html('Image #' . ($image['index'] + 1)); ?></th>
                            <td>
                                <p><code><?php echo esc_html($image['original_src']); ?></code></p>
                                <input type="url" class="large-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[image_overrides][<?php echo esc_attr($image['index']); ?>]" value="<?php echo esc_attr(isset($options['image_overrides'][$image['index']]) ? $options['image_overrides'][$image['index']] : ''); ?>" placeholder="Leave empty to keep original image">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <h2>Responsive Controls</h2>
                <table class="form-table" role="presentation">
                    <?php $this->render_input_row($options, 'desktop_breakpoint', 'Desktop Breakpoint (px)'); ?>
                    <?php $this->render_input_row($options, 'tablet_breakpoint', 'Tablet Breakpoint (px)'); ?>
                    <?php $this->render_input_row($options, 'mobile_breakpoint', 'Mobile Breakpoint (px)'); ?>
                    <?php $this->render_input_row($options, 'global_base_font_desktop', 'Desktop Base Font Size (example: 16px)'); ?>
                    <?php $this->render_input_row($options, 'global_base_font_tablet', 'Tablet Base Font Size (example: 15px)'); ?>
                    <?php $this->render_input_row($options, 'global_base_font_mobile', 'Mobile Base Font Size (example: 14px)'); ?>
                    <?php $this->render_input_row($options, 'global_section_padding_desktop', 'Global Section Padding Desktop (example: 60px 30px)'); ?>
                    <?php $this->render_input_row($options, 'global_section_padding_tablet', 'Global Section Padding Tablet (example: 40px 20px)'); ?>
                    <?php $this->render_input_row($options, 'global_section_padding_mobile', 'Global Section Padding Mobile (example: 28px 15px)'); ?>
                <h2>Hero Responsive Controls</h2>
                <table class="form-table" role="presentation">
                    <?php $this->render_input_row($options, 'hero_desktop_heading_size', 'Desktop Hero Heading Font Size (example: clamp(1.8rem, 4vw, 2.8rem))'); ?>
                    <?php $this->render_input_row($options, 'hero_tablet_heading_size', 'Tablet Hero Heading Font Size (example: 2rem)'); ?>
                    <?php $this->render_input_row($options, 'hero_mobile_heading_size', 'Mobile Hero Heading Font Size (example: 1.5rem)'); ?>
                    <?php $this->render_input_row($options, 'hero_desktop_padding', 'Desktop Hero Padding (example: 60px 30px 60px)'); ?>
                    <?php $this->render_input_row($options, 'hero_tablet_padding', 'Tablet Hero Padding (example: 40px 20px 40px)'); ?>
                    <?php $this->render_input_row($options, 'hero_mobile_padding', 'Mobile Hero Padding (example: 28px 15px 28px)'); ?>
                    <tr>
                        <th scope="row">Hero Stack Breakpoint (px)</th>
                        <td><input type="number" min="320" max="1920" name="<?php echo esc_attr(self::OPTION_KEY); ?>[hero_stack_breakpoint]" value="<?php echo esc_attr($options['hero_stack_breakpoint']); ?>" class="small-text"></td>
                    </tr>
                    <tr>
                        <th scope="row">Force stacked hero at breakpoint</th>
                        <th scope="row">Force stacked layout at breakpoint</th>
                        <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[hero_force_stacked_mobile]" value="1" <?php checked($options['hero_force_stacked_mobile'], 1); ?>> Enable</label></td>
                    </tr>
                </table>

                <h2>Theme Colors</h2>
                <table class="form-table" role="presentation">
                    <?php $this->render_input_row($options, 'color_dark_primary', 'Dark Primary Color'); ?>
                    <?php $this->render_input_row($options, 'color_accent', 'Accent Color'); ?>
                    <?php $this->render_input_row($options, 'color_light_gray', 'Light Gray Color'); ?>
                    <?php $this->render_input_row($options, 'color_white', 'White Color'); ?>
                </table>

                <h2>Link/Button Decorations</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Remove underline from buttons/CTAs</th>
                        <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[remove_button_underline]" value="1" <?php checked($options['remove_button_underline'], 1); ?>> Enable</label></td>
                    </tr>
                </table>

                <h2>Edit Every Single Thing (Full Template)</h2>
                <p>This is the full homepage HTML source. You can edit any text, section, link, class, ID, or embedded shortcode here.</p>
                <?php $this->render_textarea_row($options, 'template_html', 'Template HTML', 30, 'large-text code'); ?>

                <h2>Responsive Custom CSS</h2>
                <?php $this->render_textarea_row($options, 'responsive_css_tablet', 'Tablet CSS Override', 8, 'large-text code'); ?>
                <?php $this->render_textarea_row($options, 'responsive_css_mobile', 'Mobile CSS Override', 8, 'large-text code'); ?>
                <h2>Full Homepage Template (All options)</h2>
                <p>Everything from your original HTML is available here. You can edit any section content from the dashboard.</p>
                <?php $this->render_textarea_row($options, 'template_html', 'Template HTML', 20, 'large-text code'); ?>

                <h2>Optional Custom CSS / JS</h2>
                <?php $this->render_textarea_row($options, 'custom_css', 'Custom CSS', 8, 'large-text code'); ?>
                <?php $this->render_textarea_row($options, 'custom_js', 'Custom JS', 8, 'large-text code'); ?>

                <?php submit_button('Save Homepage Settings'); ?>
            </form>
        </div>
        <?php
    }

    private function render_input_row($options, $key, $label) {
        ?>
        <tr>
            <th scope="row"><?php echo esc_html($label); ?></th>
            <td><input type="text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($options[$key]); ?>" class="regular-text"></td>
        </tr>
        <?php
    }

    private function render_textarea_row($options, $key, $label, $rows = 6, $class = 'large-text') {
        ?>
        <tr>
            <th scope="row"><?php echo esc_html($label); ?></th>
            <td>
                <textarea name="<?php echo esc_attr(self::OPTION_KEY); ?>[<?php echo esc_attr($key); ?>]" rows="<?php echo esc_attr($rows); ?>" class="<?php echo esc_attr($class); ?>"><?php echo esc_textarea($options[$key]); ?></textarea>
            </td>
        </tr>
        <?php
    }

    public function render_shortcode() {
        $options = wp_parse_args(get_option(self::OPTION_KEY, array()), $this->get_default_options());
        $template = $options['template_html'];

        $body = $template;
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $template, $match)) {
            $body = $match[1];
        }

        $body = preg_replace('/<section id="hero">.*?<\/section>/is', $this->get_hero_section_html($options), $body, 1);
        $body = $this->apply_image_overrides($body, $options);

        $dynamic_css = $this->get_dynamic_css($options);

        $output = '<div class="voip-office-homepage-plugin">';
        $output .= '<style>' . $dynamic_css . '</style>';
        $output .= do_shortcode($body);

        if (!empty($options['custom_js'])) {
            $output .= '<script>' . $options['custom_js'] . '</script>';
        }

        $output .= '</div>';

        return $output;
    }

    private function extract_template_images($template_html) {
        $results = array();

        if (preg_match_all('/<img\b[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>/i', $template_html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $index => $match) {
                $results[] = array(
                    'index' => $index,
                    'original_src' => $match[1],
                );
            }
        }

        return $results;
    }

    private function apply_image_overrides($html, $options) {
        $image_overrides = isset($options['image_overrides']) && is_array($options['image_overrides']) ? $options['image_overrides'] : array();
        $counter = 0;

        return preg_replace_callback('/<img\b[^>]*>/i', function ($img_match) use (&$counter, $image_overrides) {
            $img_tag = $img_match[0];
            $replacement = isset($image_overrides[$counter]) ? trim($image_overrides[$counter]) : '';

            if ($replacement !== '') {
                $img_tag = preg_replace('/\bsrc=["\'][^"\']*["\']/i', 'src="' . esc_url($replacement) . '"', $img_tag, 1);
            }

            $counter++;
            return $img_tag;
        }, $html);
    }

    private function get_dynamic_css($options) {
        $base_css = '';
        if (preg_match('/<style>(.*?)<\/style>/is', $options['template_html'], $style_match)) {
            $base_css = $style_match[1];
        }

        $stack_rule = $options['hero_force_stacked_mobile'] ? "
            @media (max-width: {$options['hero_stack_breakpoint']}px) {
                #hero .hero-content { flex-direction: column !important; }
                #hero .hero-text, #hero .hero-visual { max-width: 100% !important; width: 100% !important; }
            }
        " : '';

        $button_underline_rule = $options['remove_button_underline'] ? "
            .cta-button,
            .feature-cta,
            .solution-cta,
            .video-cta-link,
            .final-cta-button,
            .hero-cta-group a,
            .final-cta-group a {
                text-decoration: none !important;
            }
        " : '';

        $tablet_bp = absint($options['tablet_breakpoint']);
        $mobile_bp = absint($options['mobile_breakpoint']);

        $override_css = "
            :root {
                --color-dark-primary: {$options['color_dark_primary']};
                --color-accent: {$options['color_accent']};
                --color-light-gray: {$options['color_light_gray']};
                --color-white: {$options['color_white']};
            }
            body { font-size: {$options['global_base_font_desktop']}; }
            section { padding: {$options['global_section_padding_desktop']}; }
            #hero { padding: {$options['hero_desktop_padding']} !important; }
            #hero .hero-text h1 { font-size: {$options['hero_desktop_heading_size']} !important; }
            @media (max-width: {$tablet_bp}px) {
                body { font-size: {$options['global_base_font_tablet']}; }
                section { padding: {$options['global_section_padding_tablet']}; }
                #hero { padding: {$options['hero_tablet_padding']} !important; }
                #hero .hero-text h1 { font-size: {$options['hero_tablet_heading_size']} !important; }
                {$options['responsive_css_tablet']}
            }
            @media (max-width: {$mobile_bp}px) {
                body { font-size: {$options['global_base_font_mobile']}; }
                section { padding: {$options['global_section_padding_mobile']}; }
                #hero { padding: {$options['hero_mobile_padding']} !important; }
                #hero .hero-text h1 { font-size: {$options['hero_mobile_heading_size']} !important; }
                {$options['responsive_css_mobile']}
            }
            {$stack_rule}
            {$button_underline_rule}
            #hero { padding: {$options['hero_desktop_padding']} !important; }
            #hero .hero-text h1 { font-size: {$options['hero_desktop_heading_size']} !important; }
            @media (max-width: 992px) {
                #hero { padding: {$options['hero_tablet_padding']} !important; }
                #hero .hero-text h1 { font-size: {$options['hero_tablet_heading_size']} !important; }
            }
            @media (max-width: 768px) {
                #hero { padding: {$options['hero_mobile_padding']} !important; }
                #hero .hero-text h1 { font-size: {$options['hero_mobile_heading_size']} !important; }
            }
            {$stack_rule}
            {$options['custom_css']}
        ";

        return $base_css . "\n" . $override_css;
    }

    private function get_hero_section_html($options) {
        return sprintf(
            '<section id="hero"><div class="hero-content"><div class="hero-text"><div class="trust-badges"><div class="badge">%1$s</div><div class="badge">%2$s</div></div><p class="sub-heading">%3$s</p><h1>%4$s <span>%5$s</span></h1><p class="description">%6$s</p><div class="hero-cta-group"><a href="%7$s" class="cta-button cta-primary">%8$s</a><a href="%9$s" class="cta-button cta-secondary">%10$s</a></div><div class="hero-metrics"><div class="hero-metric-item"><div class="hero-metric-value">%11$s</div><div class="hero-metric-label">%12$s</div></div><div class="hero-metric-item"><div class="hero-metric-value">%13$s</div><div class="hero-metric-label">%14$s</div></div><div class="hero-metric-item"><div class="hero-metric-value">%15$s</div><div class="hero-metric-label">%16$s</div></div><div class="hero-metric-item"><div class="hero-metric-value">%17$s</div><div class="hero-metric-label">%18$s</div></div></div></div><div class="hero-visual"><img src="%19$s" alt="%20$s" width="600" height="400" fetchpriority="high"></div></div></section>',
            esc_html($options['hero_badge_1']),
            esc_html($options['hero_badge_2']),
            esc_html($options['hero_sub_heading']),
            esc_html($options['hero_heading_before']),
            esc_html($options['hero_heading_highlight']),
            esc_html($options['hero_description']),
            esc_url($options['hero_cta_1_url']),
            esc_html($options['hero_cta_1_text']),
            esc_url($options['hero_cta_2_url']),
            esc_html($options['hero_cta_2_text']),
            esc_html($options['hero_metric_1_value']),
            esc_html($options['hero_metric_1_label']),
            esc_html($options['hero_metric_2_value']),
            esc_html($options['hero_metric_2_label']),
            esc_html($options['hero_metric_3_value']),
            esc_html($options['hero_metric_3_label']),
            esc_html($options['hero_metric_4_value']),
            esc_html($options['hero_metric_4_label']),
            esc_url($options['hero_image_url']),
            esc_attr($options['hero_image_alt'])
        );
    }

    private function get_default_options() {
        $template_path = plugin_dir_path(__FILE__) . 'Homepage.html';
        $template_html = file_exists($template_path) ? file_get_contents($template_path) : '';

        return array(
            'hero_badge_1' => '⭐ Trusted by 30+ countries',
            'hero_badge_2' => '99.999% Uptime',
            'hero_sub_heading' => 'Unified Communication & Contact Center Solutions',
            'hero_heading_before' => 'Cutting-Edge Solutions That Drive Your Business',
            'hero_heading_highlight' => 'Forward',
            'hero_description' => 'Gain the competitive advantage by combining HD Audio, Video, Messaging, and CRM integration all from one secure and simplified interface.',
            'hero_cta_1_text' => 'Schedule a Demo',
            'hero_cta_1_url' => 'https://voipoffice.in/contact-us/',
            'hero_cta_2_text' => 'View Pricing',
            'hero_cta_2_url' => 'https://voipoffice.in/plans-pricing/',
            'hero_image_url' => 'https://voipoffice.in/wp-content/uploads/2026/02/Group-3-2-scaled-1.webp',
            'hero_image_alt' => 'Dynamic VOIP Office Communication Visual',
            'hero_metric_1_value' => '99.999%',
            'hero_metric_1_label' => 'Uptime SLA',
            'hero_metric_2_value' => '30+',
            'hero_metric_2_label' => 'Countries Served',
            'hero_metric_3_value' => 'HD',
            'hero_metric_3_label' => 'Audio & Video',
            'hero_metric_4_value' => '24/7',
            'hero_metric_4_label' => 'Expert Support',
            'color_dark_primary' => '#14213d',
            'color_accent' => '#fca311',
            'color_light_gray' => '#e5e5e5',
            'color_white' => '#ffffff',
            'desktop_breakpoint' => '1200',
            'tablet_breakpoint' => '992',
            'mobile_breakpoint' => '768',
            'global_base_font_desktop' => '16px',
            'global_base_font_tablet' => '15px',
            'global_base_font_mobile' => '14px',
            'global_section_padding_desktop' => '60px 30px',
            'global_section_padding_tablet' => '40px 20px',
            'global_section_padding_mobile' => '28px 15px',
            'hero_desktop_heading_size' => 'clamp(1.8rem, 4vw, 2.8rem)',
            'hero_tablet_heading_size' => '2rem',
            'hero_mobile_heading_size' => '1.5rem',
            'hero_desktop_padding' => '60px 30px 60px',
            'hero_tablet_padding' => '40px 20px 40px',
            'hero_mobile_padding' => '28px 15px 28px',
            'hero_stack_breakpoint' => 992,
            'hero_force_stacked_mobile' => 1,
            'remove_button_underline' => 1,
            'image_overrides' => array(),
            'template_html' => $template_html,
            'responsive_css_tablet' => '',
            'responsive_css_mobile' => '',
            'template_html' => $template_html,
            'custom_css' => '',
            'custom_js' => "document.addEventListener('DOMContentLoaded', function() {\n    const tabs = document.querySelectorAll('.tab-button');\n    const contents = document.querySelectorAll('.industry-content');\n    tabs.forEach(tab => {\n        tab.addEventListener('click', () => {\n            tabs.forEach(t => t.classList.remove('active'));\n            contents.forEach(c => c.classList.remove('active'));\n            tab.classList.add('active');\n            const targetId = tab.dataset.industry;\n            const targetContent = document.getElementById(targetId);\n            if (targetContent) targetContent.classList.add('active');\n        });\n    });\n});\nwindow.showForm = function(formType) {\n    const allForms = document.querySelectorAll('.form-container');\n    allForms.forEach(form => form.classList.remove('active-form'));\n    const allButtons = document.querySelectorAll('.form-tab-button');\n    allButtons.forEach(button => button.classList.remove('active'));\n    const selectedForm = document.getElementById(formType + '-form');\n    if (selectedForm) selectedForm.classList.add('active-form');\n    const selectedButton = document.getElementById(formType + '-tab');\n    if (selectedButton) selectedButton.classList.add('active');\n    const formSection = document.getElementById('contact-form');\n    if (formSection) {\n        const targetPosition = formSection.getBoundingClientRect().top + window.pageYOffset - 10;\n        window.scrollTo({ top: targetPosition, behavior: 'smooth' });\n    }\n};",
        );
    }
}

register_activation_hook(__FILE__, array('VoipOfficeHomepagePlugin', 'activate'));
new VoipOfficeHomepagePlugin();
