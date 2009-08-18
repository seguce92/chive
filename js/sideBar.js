/*
 * Chive - web based MySQL database management
 * Copyright (C) 2009 Fusonic GmbH
 * 
 * This file is part of Chive.
 *
 * Chive is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * Chive is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library. If not, see <http://www.gnu.org/licenses/>.
 */

var sideBar = {
	
	loadSchemata: function(callback)
	{
		var loadingIcon = $('div.sidebarHeader.schemaList img.loading');
		var contentUl = $('#sideBar #schemaList');
		
		// Setup loading icon
		loadingIcon.attr('src', baseUrl + '/images/loading.gif');
		loadingIcon.show();
		
		// Do AJAX request
		$.post(baseUrl + '/schemata', {
			sideBar: true
		}, function(data) {
			
			var data = JSON.parse(data);
			var template = contentUl.children('li.template');
			var templateHtml = template.html();
			var html = '';
			
			// Remove all existing nodes
			contentUl.empty().append(template);
			
			// Append all nodes
			for(var i = 0; i < data.length; i++)
			{
				html += '<li class="nowrap">' + templateHtml
					.replace(/#schemaName#/g, data[i]) + '</li>';
			}
			
			contentUl.append(html);
			
			// Callback
			if($.isFunction(callback))
			{
				callback();
			}
			
			// Hide loading icon
			loadingIcon.hide();
		});
	},
	
	loadTables: function(schema, callback)
	{
		var loadingIcon = $('div.sidebarHeader.tableList img.loading');
		var contentUl = $('#sideBar #tableList');
		
		// Setup loading icon
		loadingIcon.attr('src', baseUrl + '/images/loading.gif');
		loadingIcon.show();
		
		// Do AJAX request
		$.post(baseUrl + '/schema/' + schema + '/tables', {
			sideBar: true
		}, function(data) {
			
			var data = JSON.parse(data);
			var template = contentUl.children('li.template');
			var templateHtml = template.html();
			var html = '';
			
			// Remove all existing nodes
			contentUl.empty().append(template);
			
			// Append all nodes
			for(var i = 0; i < data.length; i++)
			{
				var newHtml = '<li class="nowrap">' + templateHtml
					.replace(/#tableName#/g, data[i].tableName)
					.replace(/#rowCount#/g, data[i].rowCount)
					.replace(/#rowCountText#/g, data[i].rowCountText) + '</li>';
				if(data[i].rowCount == 0)
				{
					newHtml = newHtml.replace('icon icon16', 'icon icon16 disabled');
				}
				html += newHtml;
			}
			
			contentUl.append(html);
			
			// Callback
			if($.isFunction(callback))
			{
				callback();
			}
			
			// Hide loading icon
			loadingIcon.hide();
		});
	}
	
};