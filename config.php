<?php
require_once INCLUDE_DIR . 'class.plugin.php';

class NoteFirstPluginConfig extends PluginConfig
{
    // Provide compatibility function for versions of osTicket prior to
    // translation support (v1.9.4)
    function translate()
    {
        if (! method_exists('Plugin', 'translate')) {
            return array(
                function ($x) {
                    return $x;
                },
                function ($x, $y, $n) {
                    return $n != 1 ? $y : $x;
                }
            );
        }
        return Plugin::translate('specify_note_first');
    }

    /**
     * Build an Admin settings page.
     *
     * {@inheritDoc}
     *
     * @see PluginConfig::getOptions()
     */
    function getOptions()
    {
        list ($__, $_N) = self::translate();
        return array(
            'note-first' => new SectionBreakField(array(
                'label' => $__('To whom should we set NOTE reply first for?'),
                'description' => $__('Defaults to Agents.'),
            )),
            'note-first-enabled' => new ChoiceField(array(
                'label' => $__('Permission'),
                'default' => "staff",
                'hint' => 'Which users should have this enabled?',
                'choices' => array(
                    'disabled' => $__('Disabled'),
                    'staff' => $__('Agents Only'),
                    'all' => $__('Agents & Customers')
                )
            ))
        );
    }
}
