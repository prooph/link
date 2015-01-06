module('Manager Index', {
    setup: function() {
        ProcessManager.reset();
    }
});

test('process overview', function(){
    visit('/');
    andThen(function() {
        equal(find('#processes .list-group-item').length, 1, 'The first page should have 1 process');
    });
});