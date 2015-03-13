(function () {

    if (window.App == undefined) {
        window.App = {};
    }
    window.App.Models = {};
    window.App.Collections = {};
    window.App.Views = {};
    App.fromJson = function (data) {
        if (App.shouldRefresh()) {
            App.bots.reset(data["bots"]);
            App.onDeckJobs.total = data["on_deck"]["total"];
            App.onDeckJobs.reset(data["on_deck"]["jobs"]);
            App.finishedJobs.total = data["finished"]["total"];
            App.finishedJobs.reset(data["finished"]["jobs"]);
        }
    };
    App.fetch = function () {
        $.getJSON("/ajax/main/dashboard", function (response) {
                App.fromJson(response);
            }
        );
    };
    App.shouldRefresh = function () {
        if ($(".bot_status_button.open").length != 0)
            return false;
        return $(".ui-sortable-helper").length == 0;
    };

    App.Models.Bot = Backbone.Model.extend({
        initialize: function () {
        }
    });

    App.Collections.BotCollection = Backbone.Collection.extend({
        model: App.Models.Bot
    });

    App.Views.DashboardView = Backbone.View.extend({
        el: $('#dashtronView'),
        templateThumbnail: _.template($('#bot_thumbnail_template').html()),
        templateList: _.template($('#bot_list_template').html()),
        template: this.templateThumbnail,
        initialize: function () {
            _.bindAll(this, "render");
            this.listenTo(this.collection, 'reset', this.render, this);
        },
        render: function () {
            $(this.el).empty();
            if (window.botSize == 0) {
                $(this.el).html(this.templateList({collection: this.collection.toJSON()}));
            } else {
                this.collection.each(function (bot) {
                    $(this.el).append(this.templateThumbnail(bot.toJSON()));
                }, this);
                $(this.el).html("<div class=\"row\">" + $(this.el).html() + "</div>");
            }
            if(window['ajax_click'] != undefined) {
                $(".btn-ajax-click").click(ajax_click);
            }
        }
    });

    App.Models.Job = Backbone.Model.extend({
        initialize: function () {
        }
    });

    App.Collections.JobCollection = Backbone.Collection.extend({
        model: App.Models.Job,
        total: "0"
    });


    App.Views.JobView = Backbone.View.extend({
        template: _.template($('#job_list_template').html()),
        initialize: function (options) {
            this.type = options.type;
            _.bindAll(this, "render");
            this.listenTo(this.collection, 'reset', this.render, this);
            this.listenTo(this, 're-sort', this.sortable, this);
        },
        sortable: function () {
            if (this.type == "onDeck") {
                var jobList = $(".joblist");
                jobList.sortable({
                    handle: 'td:first',
                    update: function (event, ui) {
                        var table = this;
                        $.ajax({
                            type: 'POST',
                            url: '/ajax/queue/update_sort',
                            data: {'jobs': $(table).sortable('toArray').toString()},
                            success: function (data, status, xhr) {
                            }
                        });
                    }
                });
                jobList.disableSelection();
                $(".jobtable").disableSelection();
            }
        },
        render: function () {
            $(this.el).html(this.template({
                type: this.type,
                count: Math.min(5, this.collection.total),
                total: this.collection.total,
                collection: this.collection.toJSON()
            }));
            this.trigger("re-sort");
        }
    });

    window.botSize = 6;
    App.bots = new App.Collections.BotCollection();
    App.botsView = new App.Views.DashboardView({collection: App.bots});
    App.onDeckJobs = new App.Collections.JobCollection();
    App.onDeckJobsView = new App.Views.JobView({el: $('#onDeckJobs'), type: "onDeck", collection: App.onDeckJobs});
    App.finishedJobs = new App.Collections.JobCollection();
    App.finishedJobsView = new App.Views.JobView({
        el: $('#finishedJobs'),
        type: "finished",
        collection: App.finishedJobs
    });
    setStyle();
    App.fromJson(initialData);
})();

setInterval(function () {
    if ($('#autoload_dashboard').is(':checked')) {
        App.fetch();
    }
}, 5000);

function setStyle() {
    var dashboard_style = $("#dashboard_style").val();
    if (dashboard_style == 'small_thumbnails') {
        window.botSize = 3;
    } else if (dashboard_style == 'medium_thumbnails') {
        window.botSize = 4;
    } else if (dashboard_style == 'large_thumbnails') {
        window.botSize = 6;
    } else if (dashboard_style == 'list') {
        window.botSize = 0;
    }
    return dashboard_style;
}

function loadDashtron() {
    var dashboard_style = setStyle();

    $.post("/ajax/main/dashboard/style/" + dashboard_style);
    App.botsView.render();
}
