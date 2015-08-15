<?php

class SV_ChangePostDate_Deferred_SearchIndex extends XenForo_Deferred_Abstract
{
    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        if (!isset($data['threadId']))
        {
            return false;
        }

        $dw = XenForo_DataWriter::create("XenForo_DataWriter_Discussion_Thread");
        $dw->setExistingData($thread_id);
        $dw->sv_InsertIntoSearchIndex();


        return false;
    }

    public function canCancel()
    {
        return false;
    }
}