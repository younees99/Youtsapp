# YOUTSAPP

Youtsapp is a real-time chat app which uses http:\\socketo.me sockets

# CONFIGURATION

## Database
Before starting we have to create the Youtsapp database and put some initial entries by running this script either by browser or terminal.
```bat
    cd ..\db
    php create_tables.php
```

## Running real time socket server

In order to enable the real-time chat run the server socket in your bash using this command

```bat
    cd ..\server
    php server.php
```

# Ready to go

Now the app is ready to go, you can access as admin using 'admin' as both password and username to acess or create a new account.
