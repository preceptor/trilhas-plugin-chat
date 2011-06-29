var Chat = {
    dialogs: [],
    id: 0,
    ids: [0],
    time: null,
    url: null,
    person_id: 0,
	
    init: function(url, person_id, id) {
        this.url = url;
        this.id  = id;
        this.person_id = person_id;
        this.initEvents();
        this.verify();
    },

    initEvents: function() {
        $('#'+this.id+' ul li a.on').live('click', function(){
            Chat.Dialog.open($(this).text(), $(this).next().val());
            return false;
        });

        $('#'+Chat.id+' .dialogs textarea').live('keypress', Chat.submit)
    },
	
    verify: function() {
        $.getJSON(this.url, {'ids[]': this.ids, id: this.person_id}, function(data){
            Chat.write(data.dialogs);
            Chat.update(data.users);
            Chat.verify();
        });
    },

    update: function (data) {
        $('#'+this.id+' ul li a').removeClass('on').addClass('off');

        for (var i = 0;i < data.length;i++) {
            $('#user_' + data[i].id).removeClass('off').addClass('on');
        }
    },

    write: function(data) {
        var i = 0, html = '';
        for (;i < data.length;i++) {
            this.ids.push(data[i].id);

            if (data[i].person_id != this.person_id) {
                if(!this.Dialog.exists(data[i].person_id) > -1) {
                    this.Dialog.open(data[i].name, data[i].person_id, data[i].chat_group_id);
                }
            }
            html = '<p><strong>' + shortName(data[i].name) + ':</strong> '
                 + data[i].ds+'</p>';
            $('#chat_text_'+data[i].chat_group_id).append(html)
                                                  .scrollTop(100000);
        }
    },
	
    submit: function(e) {
        if (e.which == 13 && e.shiftKey == false) {
            $.post('chat/index/save',{ds: this.value,
                                      person_id: Chat.person_id,
                                      chat_group_id: $(this).next().val()});
            this.value = "";
            return false;
        }
    }
}

Chat.Dialog = {
    opened: [],
    open: function(name, person_id, chat_group_id) {
        var position = this.exists(person_id);
        
        if (!chat_group_id) {
            if (position > -1) {
                chat_group_id = $('#message_'+person_id).children('input').val();
            } else {
                $.ajaxSetup({async:false});
                $.get('chat/index/create-group/id/'+person_id, function(response){
                    chat_group_id = response;
                });
                $.ajaxSetup({async:true});
            }
        } 
        
        if (position > -1) {
            this.activate(position);
        } else {
            this.opened.push(person_id);
            var item = "<h3><a href='#'>" + name + "</a></h3>\n\
                        <div id='message_" + person_id + "' class='message'>\n\
                            <div id='chat_text_" + chat_group_id + "'></div>\n\
                            <textarea name='ds'></textarea>\n\
                            <input name='id' type='hidden' value='" + chat_group_id + "' />\n\
                        </div>",
                $dialogs = $('#' + Chat.id + ' .dialogs');
                
            $dialogs.prepend(item);
            $dialogs.accordion('destroy');
            $dialogs.accordion({header:'h3', autoheight:false, height:100});
        }
    },

    exists: function(id) {
        var position = $.inArray(id, this.opened);
        if (position > -1) {
            return position;
        } else {
            return -1;
        }
    },

    close: function(){
        
    },

    activate: function(position){
        var $dialogs = $('#'+Chat.id+' .dialogs'),
            position = (this.opened.length - position) - 1;
            
        $dialogs.accordion('activate',position);
    }
}

function shortName(name)
{
    return name.split(" ")[0];
}