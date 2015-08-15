<?php

class SV_ChangePostDate_XenForo_DataWriter_Discussion_Thread extends XFCP_SV_ChangePostDate_XenForo_DataWriter_Discussion_Thread
{
    public function sv_InsertIntoSearchIndex()
    {
        $this->_insertIntoSearchIndex();
    }
}