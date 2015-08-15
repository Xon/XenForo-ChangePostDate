<?php

class SV_ChangePostDate_XenForo_Model_InlineMod_Post extends XFCP_SV_ChangePostDate_XenForo_Model_InlineMod_Post
{
    public function canDateChangePostIds(array $postIds, &$errorKey = '', array $viewingUser = null)
    {
        list($posts, $threads, $forums) = $this->getPostsAndParentData($postIds, $viewingUser);
        return $this->canDateChangePosts($posts, $threads, $forums, $errorKey, $viewingUser);
    }

    public function canDateChangePosts(array $posts, array $threads, array $forums, &$errorKey = '', array $viewingUser = null)
    {
        if (count($posts) > 1 || count($posts) == 0)
        {
            $errorKey = 'sv_please_select_at_most_one_post';
            return false;
        }

        return $this->_checkPermissionOnPosts('canChangePostDate', $posts, $threads, $forums, $errorKey, $viewingUser);
    }

    public function dateChangePosts(array $postIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        list($posts, $threads, $forums) = $this->getPostsAndParentData($postIds, $viewingUser);

        if (!$this->canDateChangePosts($posts, $threads, $forums, $errorKey, $viewingUser))
        {
            return false;
        }

        $db = $this->_getDb();
        foreach ($posts AS $post)
        {
            list($thread, $forum) = $this->_getThreadAndForumFromPost($post, $threads, $forums);

            $newPostDate = $options['newPostDate'];

            $original_message_state = $post['message_state'];
            $temp_message_state = null;
            switch($original_message_state)
            {
                case 'moderated':
                    $temp_message_state = 'visible';
                    break;
                case 'visible':
                case 'deleted':
                    $temp_message_state = 'moderated';
                    break;
            }
            if (empty($temp_message_state))
            {
                throw new XenForo_Exception(new XenForo_Phrase('sv_unsupported_message_state'), true);
            }

            XenForo_Db::beginTransaction();

            XenForo_Model_Log::logModeratorAction('post', $post, 'change_date', array
                (
                    'old_date' => strftime('c', $post['post_date']),
                    'new_date' => strftime('c', $newPostDate)
                ), $thread);

            $thread_id = $post['thread_id'];
            $src_position = $post['position'];
            $dest_position = $db->fetchOne('
                SELECT position
                FROM xf_post
                WHERE thread_id = ? and post_date < ?
                ORRDER BY post_date DESC
                LIMIT 1
            ', array($thread_id, $newPostDate));
            if (empty($dest_position))
            {
                $dest_position = 0;
            }

            if ($dest_position != $src_position)
            {
                if ($dest_position > $src_position)
                {
                    $db->query('
                        UPDATE xf_post
                        SET position = position + 1
                        WHERE thread_id = ? and position >= ? and position < ?
                    ', array($thread_id, $dest_position, $src_position));
                }
                else
                {
                    $db->query('
                        UPDATE xf_post
                        SET position = position - 1
                        WHERE thread_id = ? and position >= ? and position < ?
                    ', array($thread_id, $src_position, $dest_position));
                }
            }

            $db->query('
                UPDATE xf_post
                SET position = ?, post_date = ?
                WHERE post_id = ?
            ', array($dest_position, $newPostDate, $post['post_id']));
            
            if ($dest_position != $src_position)
            {
                // update thread metadata
                $db->query('
                    UPDATE xf_thread AS thread
                    JOIN xf_post AS post ON post.thread_id = thread.thread_id and post.position = 0
                    SET thread.post_date = post.post_date, thread.first_post_id = post.post_id, thread.user_id = post.user_id, thread.username = post.username
                    WHERE thread.thread_id =  ?
                ', array($thread_id));
            }

            XenForo_Db::commit();
            
            // trigger re-indexing
        }

        return true;
    }
}