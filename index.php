<?php

/**
 * moziloCMS Plugin: GuitarNotes
 *
 * The GuitarNotes Plugin is a tool for displaying guitar notes and tabs in moziloCMS
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   HPdesigner <mail@devmount.de>
 * @license  GPL v3+
 * @version  GIT: v0.x.jjjj-mm-dd
 * @link     https://github.com/devmount/GuitarNotes
 * @link     http://devmount.de/Develop/moziloCMS/Plugins/GuitarNotes.html
 * @see      Verse
 *           – The Bible
 *
 * Plugin created by DEVMOUNT
 * www.devmount.de
 *
 */

// only allow moziloCMS environment
if (!defined('IS_CMS')) {
    die();
}

/**
 * GuitarNotes Class
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   HPdesigner <mail@devmount.de>
 * @license  GPL v3+
 * @link     https://github.com/devmount/GuitarNotes
 */
class GuitarNotes extends Plugin
{
    // language
    private $_admin_lang;
    private $_cms_lang;

    // plugin information
    const PLUGIN_AUTHOR  = 'HPdesigner';
    const PLUGIN_DOCU
        = 'http://devmount.de/Develop/moziloCMS/Plugins/GuitarNotes.html';
    const PLUGIN_TITLE   = 'GuitarNotes';
    const PLUGIN_VERSION = 'v0.x.jjjj-mm-dd';
    const MOZILO_VERSION = '2.0';
    private $_plugin_tags = array(
        'tag1' => '{GuitarNotes|tab|<syntax>}',
        'tag2' => '{GuitarNotes|pluck|<syntax>}',
        'tag3' => '{GuitarNotes|legend|<type>}',
    );

    const LOGO_URL = 'http://media.devmount.de/logo_pluginconf.png';

    /**
     * set configuration elements, their default values and their configuration
     * parameters
     *
     * @var array $_confdefault
     *      text     => default, type, maxlength, size, regex
     *      textarea => default, type, cols, rows, regex
     *      password => default, type, maxlength, size, regex, saveasmd5
     *      check    => default, type
     *      radio    => default, type, descriptions
     *      select   => default, type, descriptions, multiselect
     */
    private $_confdefault = array(
        'text' => array(
            'string',
            'text',
            '100',
            '5',
            "/^[0-9]{1,3}$/",
        ),
        'textarea' => array(
            'string',
            'textarea',
            '10',
            '10',
            "/^[a-zA-Z0-9]{1,10}$/",
        ),
        'password' => array(
            'string',
            'password',
            '100',
            '5',
            "/^[a-zA-Z0-9]{8,20}$/",
            true,
        ),
        'check' => array(
            true,
            'check',
        ),
        'radio' => array(
            'red',
            'radio',
            array('red', 'green', 'blue'),
        ),
        'select' => array(
            'bike',
            'select',
            array('car','bike','plane'),
            false,
        ),
    );

    private $_note_values = array('w', 'h', 'q', 'e', 's');

    /**
     * creates plugin content
     *
     * @param string $value Parameter divided by '|'
     *
     * @return string HTML output
     */
    function getContent($value)
    {
        global $CMS_CONF;
        global $syntax;

        // initialize cms lang
        $this->_cms_lang = new Language(
            $this->PLUGIN_SELF_DIR
            . 'lang/cms_language_'
            . $CMS_CONF->get('cmslanguage')
            . '.txt'
        );

        // get language labels
        $label = $this->_cms_lang->getLanguageValue('label');

        // get params
        list($param_type, $param_syntax)
            = $this->makeUserParaArray($value, false, '|');
        $param_syntax = str_replace('-html_nbsp~', ' ', $param_syntax);
        $param_syntax = str_replace('-html_br~', "\n", $param_syntax); // TODO

        // get conf and set default
        $conf = array();
        foreach ($this->_confdefault as $elem => $default) {
            $conf[$elem] = ($this->settings->get($elem) == '')
                ? $default[0]
                : $this->settings->get($elem);
        }

        // initialize return content, begin plugin content
        $content = '<!-- BEGIN ' . self::PLUGIN_TITLE . ' plugin content --> ';

        switch ($param_type) {
            // handle type tab
            case 'tab':
                $content .= '<div class="tactset"><span>T A B</span>';

                $tactset = explode("\n\n", trim($param_syntax));
                foreach ($tactset as $tact) {
                    $noteset = explode(' ', trim($tact));
                    $content .= '<div class="tact">';
                    foreach ($noteset as $notes) {
                        $tones = str_split(substr($notes, 0, strlen($notes)-1));
                        $value = substr($notes, -1);
                        $content .= '<div class="note">';
                        foreach ($tones as $tone) {
                            $content .= '<div class="string string'
                                . intval($tone)
                                . '"></div>';
                        }
                        $content .= '<div class="notevalue value-'
                            . $value
                            . '"></div>';
                        $content .= '</div>';
                    }
                    $content .= '</div>';
                }
                $content .= '</div><br style="clear:both;" />';
                break;
            // handle type pluck
            case 'pluck':
                $content .= '<div class="pluck">';
                $tactset = explode("\n\n", trim($param_syntax));
                foreach ($tactset as $tact) {
                    $content .= '<div class="tact">' . $tact . '</div>';
                }
                $content .= '</div>';
                break;
            // handle type legend
            case 'legend':
                $content .= '<div class="legend">';
                switch (trim($param_syntax)) {
                    case 'tab':
                        $content .= '<div class="tab note">'
                            . $this->_cms_lang->getLanguageValue('legend-note')
                            . '</div>';
                        $content .= '<div class="tab pause">'
                            . $this->_cms_lang->getLanguageValue('legend-pause')
                            . '</div>';
                        foreach ($this->_note_values as $value) {
                            $content .= '<div class="tab value-' . $value . '">'
                                . $this->_cms_lang->getLanguageValue(
                                    'legend-value-' . $value
                                )
                                . '</div>';
                        }
                        break;

                    case 'pluck':
                        // $content .= '<div class="pluck "';
                        break;

                    default:
                        # code...
                        break;
                };
                $content .= '</div>';
                break;

            default:
                # code...
                break;
        }


        // end plugin content
        $content .= '<!-- END ' . self::PLUGIN_TITLE . ' plugin content --> ';

        return $content;
    }

    /**
     * sets backend configuration elements and template
     *
     * @return Array configuration
     */
    function getConfig()
    {
        $config = array();

        // read configuration values
        foreach ($this->_confdefault as $key => $value) {
            // handle each form type
            switch ($value[1]) {
            case 'text':
                $config[$key] = $this->confText(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    )
                );
                break;

            case 'textarea':
                $config[$key] = $this->confTextarea(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    )
                );
                break;

            case 'password':
                $config[$key] = $this->confPassword(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    ),
                    $value[5]
                );
                break;

            case 'check':
                $config[$key] = $this->confCheck(
                    $this->_admin_lang->getLanguageValue('config_' . $key)
                );
                break;

            case 'radio':
                $descriptions = array();
                foreach ($value[2] as $label) {
                    $descriptions[$label] = $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_' . $label
                    );
                }
                $config[$key] = $this->confRadio(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $descriptions
                );
                break;

            case 'select':
                $descriptions = array();
                foreach ($value[2] as $label) {
                    $descriptions[$label] = $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_' . $label
                    );
                }
                $config[$key] = $this->confSelect(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $descriptions,
                    $value[3]
                );
                break;

            default:
                break;
            }
        }

        // read admin.css
        $admin_css = '';
        $lines = file('../plugins/' . self::PLUGIN_TITLE. '/admin.css');
        foreach ($lines as $line_num => $line) {
            $admin_css .= trim($line);
        }

        // add template CSS
        $template = '<style>' . $admin_css . '</style>';

        // build Template
        $template .= '
            <div class="guitarnotes-admin-header">
            <span>'
                . $this->_admin_lang->getLanguageValue(
                    'admin_header',
                    self::PLUGIN_TITLE
                )
            . '</span>
            <a href="' . self::PLUGIN_DOCU . '" target="_blank">
            <img style="float:right;" src="' . self::LOGO_URL . '" />
            </a>
            </div>
        </li>
        <li class="mo-in-ul-li ui-widget-content guitarnotes-admin-li">
            <div class="guitarnotes-admin-subheader">'
            . $this->_admin_lang->getLanguageValue('admin_test')
            . '</div>
            <div class="guitarnotes-single-conf">
                {test1_text}
                {test1_description}
                <span class="guitarnotes-admin-default">
                    [' . /*$this->_confdefault['test1'][0] .*/']
                </span>
            </div>
            <div class="guitarnotes-single-conf">
                {test2_text}
                {test2_description}
                <span class="guitarnotes-admin-default">
                    [' . /*$this->_confdefault['test2'][0] .*/']
                </span>
        ';

        $config['--template~~'] = $template;

        return $config;
    }

    /**
     * sets default backend configuration elements, if no plugin.conf.php is
     * created yet
     *
     * @return Array configuration
     */
    function getDefaultSettings()
    {
        $config = array('active' => 'true');
        foreach ($this->_confdefault as $elem => $default) {
            $config[$elem] = $default[0];
        }
        return $config;
    }

    /**
     * sets backend plugin information
     *
     * @return Array information
     */
    function getInfo()
    {
        global $ADMIN_CONF;

        $this->_admin_lang = new Language(
            $this->PLUGIN_SELF_DIR
            . 'lang/admin_language_'
            . $ADMIN_CONF->get('language')
            . '.txt'
        );

        // build plugin tags
        $tags = array();
        foreach ($this->_plugin_tags as $key => $tag) {
            $tags[$tag] = $this->_admin_lang->getLanguageValue('tag_' . $key);
        }

        $info = array(
            '<b>' . self::PLUGIN_TITLE . '</b> ' . self::PLUGIN_VERSION,
            self::MOZILO_VERSION,
            $this->_admin_lang->getLanguageValue(
                'description',
                htmlspecialchars($this->_plugin_tags['tag1'])
            ),
            self::PLUGIN_AUTHOR,
            self::PLUGIN_DOCU,
            $tags
        );

        return $info;
    }

    /**
     * creates configuration for text fields
     *
     * @param string $description Label
     * @param string $maxlength   Maximum number of characters
     * @param string $size        Size
     * @param string $regex       Regular expression for allowed input
     * @param string $regex_error Wrong input error message
     *
     * @return Array  Configuration
     */
    protected function confText(
        $description,
        $maxlength = '',
        $size = '',
        $regex = '',
        $regex_error = ''
    ) {
        // required properties
        $conftext = array(
            'type' => 'text',
            'description' => $description,
        );
        // optional properties
        if ($maxlength != '') {
            $conftext['maxlength'] = $maxlength;
        }
        if ($size != '') {
            $conftext['size'] = $size;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        if ($regex_error != '') {
            $conftext['regex_error'] = $regex_error;
        }
        return $conftext;
    }

    /**
     * creates configuration for textareas
     *
     * @param string $description Label
     * @param string $cols        Number of columns
     * @param string $rows        Number of rows
     * @param string $regex       Regular expression for allowed input
     * @param string $regex_error Wrong input error message
     *
     * @return Array  Configuration
     */
    protected function confTextarea(
        $description,
        $cols = '',
        $rows = '',
        $regex = '',
        $regex_error = ''
    ) {
        // required properties
        $conftext = array(
            'type' => 'textarea',
            'description' => $description,
        );
        // optional properties
        if ($cols != '') {
            $conftext['cols'] = $cols;
        }
        if ($rows != '') {
            $conftext['rows'] = $rows;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        if ($regex_error != '') {
            $conftext['regex_error'] = $regex_error;
        }
        return $conftext;
    }

    /**
     * creates configuration for password fields
     *
     * @param string  $description Label
     * @param string  $maxlength   Maximum number of characters
     * @param string  $size        Size
     * @param string  $regex       Regular expression for allowed input
     * @param string  $regex_error Wrong input error message
     * @param boolean $saveasmd5   Safe password as md5 (recommended!)
     *
     * @return Array   Configuration
     */
    protected function confPassword(
        $description,
        $maxlength = '',
        $size = '',
        $regex = '',
        $regex_error = '',
        $saveasmd5 = true
    ) {
        // required properties
        $conftext = array(
            'type' => 'text',
            'description' => $description,
        );
        // optional properties
        if ($maxlength != '') {
            $conftext['maxlength'] = $maxlength;
        }
        if ($size != '') {
            $conftext['size'] = $size;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        $conftext['saveasmd5'] = $saveasmd5;
        return $conftext;
    }

    /**
     * creates configuration for checkboxes
     *
     * @param string $description Label
     *
     * @return Array  Configuration
     */
    protected function confCheck($description)
    {
        // required properties
        return array(
            'type' => 'checkbox',
            'description' => $description,
        );
    }

    /**
     * creates configuration for radio buttons
     *
     * @param string $description  Label
     * @param string $descriptions Array Single item labels
     *
     * @return Array Configuration
     */
    protected function confRadio($description, $descriptions)
    {
        // required properties
        return array(
            'type' => 'select',
            'description' => $description,
            'descriptions' => $descriptions,
        );
    }

    /**
     * creates configuration for select fields
     *
     * @param string  $description  Label
     * @param string  $descriptions Array Single item labels
     * @param boolean $multiple     Enable multiple item selection
     *
     * @return Array   Configuration
     */
    protected function confSelect($description, $descriptions, $multiple = false)
    {
        // required properties
        return array(
            'type' => 'select',
            'description' => $description,
            'descriptions' => $descriptions,
            'multiple' => $multiple,
        );
    }

    /**
     * throws styled message
     *
     * @param string $type Type of message ('ERROR', 'SUCCESS')
     * @param string $text Content of message
     *
     * @return string HTML content
     */
    protected function throwMessage($text, $type)
    {
        return '<div class="'
                . strtolower(self::PLUGIN_TITLE . '-' . $type)
            . '">'
            . '<div>'
                . $this->_cms_lang->getLanguageValue(strtolower($type))
            . '</div>'
            . '<span>' . $text. '</span>'
            . '</div>';
    }

}

?>