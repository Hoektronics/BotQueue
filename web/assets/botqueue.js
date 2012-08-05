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

function ajaxLoad()
{
	$('ajax_loader').show();
}

function ajaxUnload()
{
	$('ajax_loader').hide();
}

function check_upload_form(ele)
{
	var form = $(ele);

	var filename = form['file'].value;
	
	//error checking.
	if (!filename || filename.length == 0)
	{
		alert("You must select a file.");
		return false;
	}
	
	//figure out our extension
	var dot = filename.lastIndexOf('.');
	var extension = '';
	if (dot != -1)
		extension = filename.substr(dot+1,filename.length); 

	//figoure out our MIME/content-type and do something with it.
	var contentType = find_mime_type(extension);
	form['Content-Type'].value = contentType;

	//this is for prompting a download.
	if (contentType != 'application/octet-stream')
		form['Content-Disposition'].value = "";
	else
		form['Content-Disposition'].value = "Content-disposition: attachment; filename=" + filename;

	return true;
}

function find_mime_type(ext)
{
	ext = ext.toLowerCase();
	
	var mime_types = {
		pdf: 'application/pdf',
		zip: 'application/zip',
		tar: 'application/x-tar',
		tgz: 'application/x-tar',
		rar: 'application/x-rar-compressed',
		p3d: 'application/x-p3d',
		stl: 'application/sla',
		eps: 'application/postscript',
		ai: 'application/postscript',
		ps: 'application/postscript',
		ccad: 'application/clariscad',
		latex: 'application/x-latex',
		dvi: 'application/x-dvi',
		rtf: 'application/rtf',
		cdr: 'application/coreldraw',

		gif: 'image/gif',
		jpe: 'image/jpeg',
		jpg: 'image/jpeg',
		png: 'image/png',
		jpeg: 'image/jpeg',
		tiff: 'image/tiff',
		dwg: 'image/vnd.dwg',
		dxf: 'image/vnd.dxf',
		svg: 'image/svg+xml',
		svf: 'image/vnd.svf',
		epsi: 'image/x-eps',
		epsf: 'image/x-eps',
		lwo: 'image/x-lwo',

		
		iv: 'graphics/x-inventor',

		iges: 'model/iges',
		igs: 'model/iges',
		wrl: 'model/vrml',
		vrml: 'model/vrml',
		msh: 'model/mesh',
		mesh: 'model/mesh',
		silo: 'model/silo',
		dwf: 'model/vnd.dwf',
		'3dml': 'model/vnd.flatland.3dml',
		'3dm': 'model/vnd.flatland.3dml',
		gdl: 'model/vnd.gdl',
		dsm: 'model/vnd.gdl',
		win: 'model/vnd.gdl',
		dor: 'model/vnd.gdl',
		lmp: 'model/vnd.gdl',
		rsm: 'model/vnd.gdl',
		msm: 'model/vnd.gdl',
		ism: 'model/vnd.gdl',
		gtw: 'model/vnd.gtw',
		moml: 'model/vnd.moml+xml',
		mts: 'model/vnd.mts',
		x_b: 'model/vnd.parasolid.transmit-binary',
		x_t: 'model/vnd.parasolid.transmit-text',
		vtu: 'model/vnd.vtu',
		x3d: 'model/x3d+xml',
		pov: 'model/x-pov',
		
		txt: 'text/plain',
		htm: 'text/html',
		html: 'text/html'
	};
	
	var type = mime_types[ext];
	
	//standard default.
	if (!type)
		type = 'application/octet-stream';
		
	return type;
}

function selectAll(ele)
{
	ele.focus();
	ele.select();
}

function checkEnter(e){ //e is event object passed from function invocation
	var characterCode //literal character code will be stored in this variable

	if(e && e.which){ //if which property of event object is supported (NN4)
		e = e
		characterCode = e.which //character code is contained in NN4's which property
	}
	else{
		e = event
		characterCode = e.keyCode //character code is contained in IE's keyCode property
	}

	if(characterCode == 13){ //if generated character code is equal to ascii 13 (if enter key)
		return true
	}
	else{
		return false
	}
}