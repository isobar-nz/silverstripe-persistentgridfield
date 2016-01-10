<?php

/**
 * Class ResetGridStateButton
 *
 * This is a stub button, the action is handled in PersistentGridField->gridFieldAlterAction()
 */
class ResetGridStateButton implements GridField_HTMLProvider, GridField_ActionProvider, GridField_URLHandler
{
    /**
     * Fragment to write the button to.
     *
     * @var string
     */
    protected $targetFragment;

    /**
     * @param string $targetFragment The HTML fragment to write the button into
     */
    public function __construct($targetFragment = "after")
    {
        $this->targetFragment = $targetFragment;
    }

    /**
     * @param $gridField
     * @return array
     */
    public function getHTMLFragments($gridField)
    {
        $button = new GridField_FormAction(
            $gridField,
            'ResetState',
            'Reset Grid',
            'ResetState',
            null
        );

        return array(
            $this->targetFragment => $button->Field()
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

    /**
     * @param GridField $gridField
     * @param $actionName
     * @param $arguments
     * @param $data
     */
    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
    }

    /**
     * @param GridField $gridField
     * @param null $request
     */
    public function handleResetState(GridField $gridField, $request = null)
    {
    }
}
