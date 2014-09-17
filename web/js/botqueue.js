var Bot = Backbone.Model.extend({
    initialize: function () {
    }
});

var BotCollection = Backbone.Collection.extend({
    model: Bot,
    url: 'ajax/main/dashboardbb'
});

var DashboardView = Backbone.View.extend({
    el: $('#dashtronView'),
    templateThumbnail: _.template($('#bot_thumbnail_template').html()),
    templateList: _.template($('#bot_list_template').html()),
    template: this.templateThumbnail,
    initialize: function () {
        _.bindAll(this, "render");
        this.listenTo(this.collection, 'sync', this.render);
        this.collection.fetch();
    },
    render: function () {
        $(this.el).empty();
        if(window.botSize == 0) {
            $(this.el).html(this.templateList({collection:this.collection.toJSON()}));
        } else {
            this.collection.each(function (bot) {
                $(this.el).append(this.templateThumbnail(bot.toJSON()));
            }, this);
            $(this.el).html("<div class=\"row\">" + $(this.el).html() + "</div>");
        }
    }
});

window.botSize = 6;
var myBots = new BotCollection();
var myView = new DashboardView({collection: myBots});
setInterval(function () {
    if ($('#autoload_dashboard').is(':checked'))
    {
        myBots.fetch();
    }
}, 5000);

function loadDashtron() {
    var dashboard_style = $("#dashboard_style").val();
    if(dashboard_style == 'small_thumbnails') {
        window.botSize = 3;
    } else if(dashboard_style == 'medium_thumbnails') {
        window.botSize = 4;
    } else if(dashboard_style == 'large_thumbnails') {
        window.botSize = 6;
    } else if(dashboard_style == 'list') {
        window.botSize = 0;
    }

    myView.render();
}