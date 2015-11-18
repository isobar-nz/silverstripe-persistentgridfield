<?php

/**
 * Class PersistentGridField
 */
class PersistentGridField extends GridField
{

    /**
     * @param string $name
     * @param null $title
     * @param SS_List|null $dataList
     * @param GridFieldConfig|null $config
     */
    public function __construct($name, $title = null, SS_List $dataList = null, GridFieldConfig $config = null) {
        parent::__construct($name, $title, $dataList, $config);
        $resetButton = new ResetGridStateButton();
        $this->getConfig()->addComponent($resetButton);
    }

    public function getStateHash()
    {
        $vars = $this->getRequest()->getVars();
        unset($vars['url']);
        return 'previousGridState_' . substr(md5(serialize(array(
            $this->Link(),
            $vars
        ))), 0, 8);
    }

    /**
     * @param array $data
     * @param Form $form
     * @param SS_HTTPRequest $request
     * @return HTML|HTMLText|mixed
     */
    public function gridFieldAlterAction($data, $form, SS_HTTPRequest $request)
    {
        $data = $request->requestVars();
        $stateHash = $this->getStateHash();
        $name = $this->getName();
        $fieldData = null;

        if(isset($data[$name])) {
            $fieldData = $data[$name];
        }

        // Check if we have encountered a reset action. We need to clear the state here before
        // the other components start accessing it.
        foreach($data as $dataKey => $dataValue) {
            if(preg_match('/^action_gridFieldAlterAction\?StateID=(.*)/', $dataKey, $matches)) {
                $stateChange = Session::get($matches[1]);
                $actionName = $stateChange['actionName'];
                if($actionName === 'ResetState') {
                    Session::set($stateHash, null);
                    $this->state = new GridState($this);
                }
            }
        }

        foreach($data as $dataKey => $dataValue) {
            if(preg_match('/^action_gridFieldAlterAction\?StateID=(.*)/', $dataKey, $matches)) {
                $stateChange = Session::get($matches[1]);
                $actionName = $stateChange['actionName'];

                $arguments = array();

                if(isset($stateChange['args'])) {
                    $arguments = $stateChange['args'];
                };

                $html = $this->handleAlterAction($actionName, $arguments, $data);

                if($html) {
                    return $html;
                }
            }
        }

        // The state is stored in the session so that we can access it on the next page load
        Session::set($stateHash, $this->state->Value());

        if($request->getHeader('X-Pjax') === 'CurrentField') {
            return $this->FieldHolder();
        }

        return $form->forTemplate();
    }

    /**
     * @param array $properties
     * @return HTMLText
     */
    public function FieldHolder($properties = array())
    {
        $stateHash = $this->getStateHash();

        if($previousState = Session::get($stateHash)) {
            $this->state->setValue($previousState);
        }

        return parent::FieldHolder($properties);

    }

}
