# YOUTSAPP

Youtsapp is a real-time chat app which uses www.socketo.me sockets

# CONFIGURATION

On first run you should create the tables by running 

```bat
    cd db
    php create_tables.php
```

In order to enable the real-time chat run the server socket in your bash using this command

```bat
    cd ..\server
    php server.php
```