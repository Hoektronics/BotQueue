<div class="span<%= window . botSize %> bot_thumbnail bot_thumbnail_<%= window . botSize %>">
	<div class="bot_thumbnail_content">
		<div class="bot_thumbnail_stretcher"></div>
		<div class="real_bot_thumbnail_content">
			<div class="bot_header">
				<h3>
					<a href="<%= url %>"><%= name %></a>
					<span class="muted">- <%= last_seen %>
						<% if (typeof(job) !== "undefined") { %>
							<% if (window . botSize > 3 && job . status == 'taken') { %>
								- Runtime: <%= job . elapsed %>
							<% } %>
						<% } %>
					</span>
				</h3>

				<div class="btn-group bot_status_button">
					<a id="bot_status_button_<%= id %>"
					   class="btn btn-mini btn-bot-status btn-<%= status_class %> dropdown-toggle"
					   data-toggle="dropdown" href="#">
						<span id="bot_status_txt<%= id %>"><%= status %></span>
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<% _ . each(menu, function (item) { %>
							<li><a href="<%= item . url %>"><i class="<%= item . icon %>"></i> <%= item . text %></a>
							</li>
						<% }) %>
					</ul>
				</div>
				<div class="clearfix"></div>
			</div>
			<a href="<%= url %>"><img class="webcam" src="<%= webcam_url %>"</a>

			<% if (typeof(job) !== "undefined") { %>
			<div class="bot_info_container">
				<div class="bot_info">
					<div class="bot_info_title">
						<span class="label <%= job . status_class %>"><%= job . status %></span>
						<a href="<%= job . url %>"><%= job . name %></a>
						<% if (status == 'working') { %>
							<span class="muted pull-right">
									<% if (window . botSize == 6) { %>
										<% if (typeof(temp_extruder) !== "undefined") { %>
											E: <%= temp_extruder %>C /
										<% } %><% if (typeof(temp_bed) !== "undefined") { %>
											B: <%= temp_bed %>C /
										<% } %>
									<% } %>
								<% if (window . botSize >= 4) { %>
									ETA: <%= job . estimated %> /
								<% } %>
								<%= job . progress %>%
							</span>
						<% } %>
						<% if (typeof(job . qa_url) !== "undefined") { %>
							<div class="manage-job pull-right">
								<a class="btn btn-success btn-mini" href="<%= job . qa_url %>/pass">PASS</a>
								<a class="btn btn-primary btn-mini" href="<%= job . qa_url %>">VIEW</a>
								<a class="btn btn-danger btn-mini" href="<%= job . qa_url %>/fail">FAIL</a>
							</div>
						<% } %>
						<div class="clearfix"></div>
					</div>
					<% if (typeof(job . progress) !== "undefined") { %>
						<div class="bot_info_meta">
							<div class="progress progress-striped active pull-right" style="width: 100%">
								<div class="bar <%= job . bar_class %>" style="width: <%= job . progress %>%;"></div>
							</div>
						</div>
					<% } %>
				</div>
				<% } %>

				<% if (typeof(error_text) !== "undefined") { %>
					<div class="bot_info_container">
						<div class="bot_info">
							<span class="text-error">Error: <%= error_text %></span>
						</div>
					</div>
				<% } %>
			</div>
		</div>
	</div>
</div>