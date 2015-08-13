<?php

class SV_ChangePostDate_XenForo_Model_Post extends XFCP_SV_ChangePostDate_XenForo_Model_Post
{
    public function addInlineModOptionToPost(array &$post, array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);
        $postModOptions = parent::addInlineModOptionToPost($post, $thread, $forum, $nodePermissions, $viewingUser);

        if ($viewingUser['user_id'] && XenForo_Permission::hasContentPermission($nodePermissions, 'SV_ChangePostDate'))
        {
            $postModOptions['datechange'] = true;
            $post['canInlineMod'] = (count($postModOptions) > 0);
        }

        return $postModOptions;
    }


    public function canChangePostDate(array $post, array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if (XenForo_Permission::hasContentPermission($nodePermissions, 'SV_ChangePostDate'))
        {
            return true;
        }

        return false;
    }

}