.PHONY=dev

dev:
    tmux\
        new session "php artisan serve" \;\
        split-window -h "npm run hot" \;\
        split-window -v "laravel-echo-server start" \;\
