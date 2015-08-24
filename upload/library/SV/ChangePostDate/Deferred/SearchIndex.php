<?php

class SV_ChangePostDate_Deferred_SearchIndex extends XenForo_Deferred_Abstract
{
    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        if (empty($data['threadId']))
        {
            return false;
        }

        // if re-indexing is required, then the datawriter requires reloading to pickup the potentially new 1st post
        if(!empty($data['reindexThread']))
        {
            $dw = XenForo_DataWriter::create("XenForo_DataWriter_Discussion_Thread");
            $dw->setExistingData($data['threadId']);
            $dw->rebuildDiscussion();
        }
        $dw = XenForo_DataWriter::create("XenForo_DataWriter_Discussion_Thread");
        $dw->setExistingData($data['threadId']);
        $dw->sv_updateSearchIndexTitle();

        return false;
    }

    public function canCancel()
    {
        return false;
    }
}