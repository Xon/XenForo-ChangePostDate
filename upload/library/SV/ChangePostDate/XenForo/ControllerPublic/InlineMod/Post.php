<?php

class SV_ChangePostDate_XenForo_ControllerPublic_InlineMod_Post extends XFCP_SV_ChangePostDate_XenForo_ControllerPublic_InlineMod_Post
{
    public function actionDateChange()
    {
        if ($this->isConfirmedPost())
        {
            $newPostDate_ISO8601 = $this->_input->filterSingle('datechange', XenForo_Input::STRING);
            $newPostDate = strtotime($newPostDate_ISO8601);

            if (!$newPostDate)
            {
                throw $this->getErrorOrNoPermissionResponseException('sv_please_enter_valid_date_format');
            }

            $options = array(
                'newPostDate' => $newPostDate,
            );

            return $this->executeInlineModAction('dateChangePosts', $options, array('fromCookie' => false));
        }

        $postIds = $this->getInlineModIds();

        $handler = $this->_getInlineModPostModel();

        if (!$handler->canDateChangePostIds($postIds, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if (!$postIds)
        {
            $redirect = $this->getDynamicRedirect();

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $redirect
            );
        }

        $visitor = XenForo_Visitor::getInstance();

        $posts = $this->_getPostModel()->getPostsByIds($postIds, array(
            'join' => XenForo_Model_Post::FETCH_THREAD
        ));

        foreach ($posts AS &$post)
        {
            $dt = new DateTime('@' . $post['post_date']);
            $dt->setTimezone(new DateTimeZone($visitor['timezone']));
            // ISO 8601
            $post['formatted_date'] = $dt->format('c');
        }

        $post = reset($posts);
        $redirect = XenForo_Link::buildPublicLink('posts', $post);

        $viewParams = array(
            'postIds' => $postIds,
            'post' => $post,
            'postCount' => count($postIds),
            'redirect' => $redirect,
        );

        return $this->responseView('SV_ChangePostDate_ViewPublic_Post', 'inline_mod_post_datechange', $viewParams);
    }
}