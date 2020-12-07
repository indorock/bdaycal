var sel = document.querySelector('#yearselect');
if(sel){
    sel.onchange = function(ev){
        window.location.href = location.protocol + '//' + location.host + location.pathname+'?y='+ev.target.options[ev.target.selectedIndex].value;
    }
}