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

class NotificationsController extends Controller
{
    public function home()
    {
        $this->assertLoggedIn();
        $user = User::$me;

        $notifications = Notification::getMine();

        if($notifications->count() > 0) {
            // Mark the maximum value as unread
            $user->set('last_notification', $notifications->getMax('id'));
            $user->save();
        }
        $this->set('notifications', $notifications->getAll());
    }

    public function all()
    {
        $this->assertLoggedIn();
        $user = User::$me;

        $notifications = Notification::getMine(true);

        if($notifications->count() > 0) {
            // Mark the maximum value as unread
            $user->set('last_notification', $notifications->getMax('id'));
            $user->save();
        }
        $this->set('notifications', $notifications->getAll());
    }

    public function count()
    {
        $this->set('content', Notification::getCount());
    }

    public function draw()
    {
        $this->setArg('notifications');
    }
}