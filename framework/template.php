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

class Template
{
	private $controller;
	private $view;

	public function __construct($controller, $view) {
		$this->controller = $controller;
		$this->view = $view;
	}

	public function render() {
		ob_start();

		$template_file = VIEWS_DIR . strtolower("{$this->controller}/{$this->view}.ejs");

		if(file_exists($template_file)) {
			include($template_file);
		} else {
			throw new ViewException("The {$this->controller}.{$this->view} page does not exist!");
		}

		return ob_get_clean();
	}
}