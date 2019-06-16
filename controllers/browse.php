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

class BrowseController extends Controller
{
    public function pagination_info()
    {
        /** @var Collection $collection */
        $collection = $this->args('collection');

        //pass through our args.
        $this->set('total', $collection->total());
        $this->set('start', $collection->start());
        $this->set('end', $collection->end());
        $this->setArg('word');
    }

    public function pagination()
    {
        /** @var Collection $collection */
        $collection = $this->args('collection');

        $this->set('page', $collection->page());
        $this->set('total', $collection->total());
        $this->set('per_page', $collection->perPage());
        $this->setArg('base_url');
    }
}