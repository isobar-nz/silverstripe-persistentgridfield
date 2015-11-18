<?php

/**
 * This is a stub button, the action is handled in PersistentGridField->gridFieldAlterAction()
 * Class ResetGridStateButton
 */
class ResetGridStateButton implements GridField_HTMLProvider, GridField_ActionProvider, GridField_URLHandler
{

    /**
     * @param $gridField
     * @return array
     */
    public function getHTMLFragments($gridField)
    {
        $button = new GridField_FormAction(
            $gridField,
            'ResetState',
            'Reset',
            'ResetState',
            null
        );

        return array(
            'after' => '<div style="padding-left: 0;" class="cms-content-actions cms-content-controls south">' . $button->Field() . '</div>'
        );

    }

    /**
     * @param $gridField
     * @return array
     */
    public function getActions($gridField)
    {
        return array('ResetState');
    }

    /**
     * @param $gridField
     * @return array
     */
    public function getURLHandlers($gridField)
    {
        return array(
            'ResetState' => 'handleResetState',
        );
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data) {}

    public function handleResetState(GridField $gridField, $request = null) {}

}
