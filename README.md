# YOUTSAPP

Youtsapp is a real-time chat app which uses [socketo.me](http://socketo.me/) sockets

# CONFIGURATION

## Database
Before starting we have to create the Youtsapp database and put some initial entries by running this script either by browser or terminal.
```bat
    php src\controller\admin\create_tables.php
```

## Running real time socket server

In order to enable the real-time chat run the server socket in your bash using this command

```bat
    php server\server.php
```

# Ready to go

Now the app is ready to go, you can access as admin using 'admin' as both password and username to acess or create a new account.
