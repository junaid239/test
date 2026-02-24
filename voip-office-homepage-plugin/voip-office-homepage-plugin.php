<?php
/**
 * Plugin Name: VoIP Office Homepage Builder
 * Description: Converts a static VoIP Office homepage into a shortcode-driven WordPress plugin with dashboard-managed options.
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
            'hero_desktop_padding', 'hero_tablet_padding', 'hero_mobile_padding'
        );

        foreach ($text_fields as $field) {
            $output[$field] = sanitize_text_field($output[$field]);
        }

        $output['hero_stack_breakpoint'] = absint($output['hero_stack_breakpoint']);
        $output['hero_stack_breakpoint'] = $output['hero_stack_breakpoint'] > 0 ? $output['hero_stack_breakpoint'] : 992;

        $output['hero_force_stacked_mobile'] = !empty($input['hero_force_stacked_mobile']) ? 1 : 0;

        if (current_user_can('unfiltered_html')) {
            $output['template_html'] = (string) $output['template_html'];
            $output['custom_css'] = (string) $output['custom_css'];
            $output['custom_js'] = (string) $output['custom_js'];
        } else {
            $output['template_html'] = wp_kses_post($output['template_html']);
            $output['custom_css'] = sanitize_textarea_field($output['custom_css']);
            $output['custom_js'] = sanitize_textarea_field($output['custom_js']);
        }

        return $output;
    }

    public function render_admin_page() {
        $options = wp_parse_args(get_option(self::OPTION_KEY, array()), $this->get_default_options());
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

        $override_css = "
            :root {
                --color-dark-primary: {$options['color_dark_primary']};
                --color-accent: {$options['color_accent']};
                --color-light-gray: {$options['color_light_gray']};
                --color-white: {$options['color_white']};
            }
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
            'hero_desktop_heading_size' => 'clamp(1.8rem, 4vw, 2.8rem)',
            'hero_tablet_heading_size' => '2rem',
            'hero_mobile_heading_size' => '1.5rem',
            'hero_desktop_padding' => '60px 30px 60px',
            'hero_tablet_padding' => '40px 20px 40px',
            'hero_mobile_padding' => '28px 15px 28px',
            'hero_stack_breakpoint' => 992,
            'hero_force_stacked_mobile' => 1,
            'template_html' => $template_html,
            'custom_css' => '',
            'custom_js' => "document.addEventListener('DOMContentLoaded', function() {\n    const tabs = document.querySelectorAll('.tab-button');\n    const contents = document.querySelectorAll('.industry-content');\n    tabs.forEach(tab => {\n        tab.addEventListener('click', () => {\n            tabs.forEach(t => t.classList.remove('active'));\n            contents.forEach(c => c.classList.remove('active'));\n            tab.classList.add('active');\n            const targetId = tab.dataset.industry;\n            const targetContent = document.getElementById(targetId);\n            if (targetContent) targetContent.classList.add('active');\n        });\n    });\n});\nwindow.showForm = function(formType) {\n    const allForms = document.querySelectorAll('.form-container');\n    allForms.forEach(form => form.classList.remove('active-form'));\n    const allButtons = document.querySelectorAll('.form-tab-button');\n    allButtons.forEach(button => button.classList.remove('active'));\n    const selectedForm = document.getElementById(formType + '-form');\n    if (selectedForm) selectedForm.classList.add('active-form');\n    const selectedButton = document.getElementById(formType + '-tab');\n    if (selectedButton) selectedButton.classList.add('active');\n    const formSection = document.getElementById('contact-form');\n    if (formSection) {\n        const targetPosition = formSection.getBoundingClientRect().top + window.pageYOffset - 10;\n        window.scrollTo({ top: targetPosition, behavior: 'smooth' });\n    }\n};",
        );
    }
}

register_activation_hook(__FILE__, array('VoipOfficeHomepagePlugin', 'activate'));
new VoipOfficeHomepagePlugin();
