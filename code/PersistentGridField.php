<?php

/**
 * Class PersistentGridField
 *
 * Stores the state of a GridField between requests
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
        $this->getConfig()->addComponent(new GridFieldButtonRow('after'));
        $this->getConfig()->addComponent(new ResetGridStateButton('buttons-after-right'));
    }

    /**
     * Return the current hash for state
     *
     * @return string
     */
    public function getStateHash()
    {
        $getVars = Controller::curr()->getRequest()->getVars();
        $getAction = Controller::curr()->getRequest()->getVar('action_search');
        $gridActionHash = 'gridActionHash_' . substr(md5(serialize($getVars)), 0, 8);
        $hash = 'previousGridState_' . substr(md5(serialize(array($this->Link()))), 0, 8);

        $reset = false;
        if ($getAction) {
            $hashes = $this->clearUnusedActionHashes($gridActionHash);

            // if we don't have the current action hash in our memory then this is a new action and we need to clear
            // the current state of the application state storage
            if (!in_array($gridActionHash, $hashes)) {
                $reset = true;
                $this->updateHashSet($hash, null);
            }

            $this->pushCurrentActionHash($gridActionHash, $hashes);
        }

        if ($reset) {
            return false;
        }

        return $hash;
    }

    /**
     * Push the current action hash into our record of current actions for the page we are on.
     *
     * @param $hash
     * @param $hashes
     */
    public function pushCurrentActionHash($hash, $hashes)
    {
        $array = array_merge($hashes, array($this->Link() => $hash));
        Session::set('PersistentGridActions', $array);
    }

    /**
     * Clear any hash from the storage set which isn't the hash we are using
     *
     * @param $currentHash
     * @return array
     */
    public function clearUnusedActionHashes($currentHash)
    {
        $persistentHashes = Session::get('PersistentGridActions') ?: array();

        if ($persistentHashes) {
            foreach ($persistentHashes as $link => $hash) {
                if ($hash != $currentHash && $this->Link() == $link) {
                    unset($persistentHashes[$link]);
                }
            }
        }

        return $persistentHashes;
    }


    /**
     * Set the state hash for the current grid state
     *
     * @param $state
     */
    public function setStateHash($state)
    {
        if($hash = $this->getStateHash()) {
            Session::set($hash, $state);
        }
        $this->updateHashSet($hash, $state);
    }

    /**
     * Update a hash set in memory and add the new value to it. If the current link has a hash which matches then set
     * that hashes value to the supplied one. Add the new hash to the array of hashes stored in memory.
     *
     * @param $newHash
     * @param $value
     */
    public function updateHashSet($newHash, $value) {
        $currentHashes = Session::get('PersistentHashes') ?: array();
        if ($currentHashes) {
            foreach ($currentHashes as $link => $hash) {
                if ($link == $this->Link()) {
                    Session::set($hash, $value);
                }
            }
        }

        $persistentHashes = array_merge($currentHashes, array($this->Link() => $newHash));
        Session::set('PersistentHashes', $persistentHashes);
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
        $this->setStateHash($this->state->Value());

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
