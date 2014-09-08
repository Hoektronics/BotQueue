<?php

/*
  This file is part of BotQueue.

  BotQueue is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  BotQueue is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with BotQueue.  If not, see <http://www.gnu.org/licenses/>.
*/

class CommentController extends Controller
{
    function draw_all()
    {
        $this->setArg('comments');
    }

    function add_comment()
    {
        $this->assertLoggedIn();

        try {
            $this->setTitle('Add Comment');

            $content_id = $this->args('content_id');
            $content_type = $this->args('content_type');
            $content = Comment::getContent($content_id, $content_type);

            if (!is_object($content))
                throw new Exception("That content does not exist.");
            if (!$content->isHydrated())
                throw new Exception("That content does not exist.");
            if (!$content->canComment())
                throw new Exception("You cannot comment on this content.");

            $form = $this->_createAddCommentForm($content_id, $content_type);

            //handle our form
            if ($form->checkSubmitAndValidate($this->args())) {
                $comment = new Comment();
                $comment->set('content_id', $form->data('content_id'));
                $comment->set('content_type', $form->data('content_type'));
                $comment->set('comment', $form->data('comment'));
                $comment->set('user_id', User::$me->id);
                $comment->set('comment_date', date("Y-m-d H:i:s"));
                $comment->save();

                Activity::log("commented on " . $content->getLink() . ".");

                $this->forwardToUrl($content->getUrl());
            }

            $this->set('form', $form);
        } catch (Exception $e) {
            $this->setTitle('Add Comment - Error');
            $this->set('megaerror', $e->getMessage());
        }
    }

    function _createAddCommentForm($content_id, $content_type)
    {
        $form = new Form();
        $form->action = "/comment/add";
        $form->submitText = "Add Comment";

		$form->add(
			HiddenField::name('content_id')
			->value($content_id)
		);

		$form->add(
			HiddenField::name('content_type')
			->value($content_type)
		);

		$form->add(
			TextareaField::name('comment')
			->required(true)
			->width('50%')
			->rows(3)
		);

        return $form;
    }
}