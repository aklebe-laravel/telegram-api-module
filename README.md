## Telegram API Module

A module for [Mercy Scaffold Application](https://github.com/aklebe-laravel/mercy-scaffold.git)
(or any based on it like [Jumble Sale](https://github.com/aklebe-laravel/jumble-sale.git)).

This module provides telegram on your website.

Also requires the [Module WebsiteBase](https://github.com/aklebe-laravel/website-base-module.git)

### Config your website

- env: enter a telegram name and token for each bot you will use
  - ```TELEGRAM_BOT_NAME_LOCAL_MYBOT1="my_bot_1"```
  - ```TELEGRAM_BOT_TOKEN_LOCAL_MYBOT1="xxx"```
- ```config/telegram.php```
  - optionally enter the default bot like ```my_bot_1``` above
- Core Configuration
- To enable telegram notifications you need to config the core config.
    - set ```telegram.enabled``` to 1
    - set ```channels.telegram.enabled``` to 1
    - set ```notification.channels.telegram.enabled``` to 1
    - set ```notification.channels.telegram.bot``` to bot like ```my_bot_1``` above, otherwise you have to set up a default bot

#### Set up a telegram group or channel

To send notifications to your telegram channel, create a user and give him a telegram id.  


### NotificationEvent

#### Data Fields
- **event_data**: depends on the event_code
  - **event_code**: (any)
    - ``` {"view_path":"xxx"} ``` if send to telegram: view path (default=```telegram-api::telegram.default-message```)
    - ``` {"buttons":"website_link"} ``` if send to telegram: code of a button container declared in: ```\Modules\TelegramApi\Services\TelegramButtonService::DEFINED_BUTTON_CONTAINERS```
  - **event_code**: AclGroups_attached_to_User
    - ``` {"acl_group":"Traders"} ``` will be triggert if group "Traders" was assigned to user
    - ``` {"acl_group":"*"} ``` (no specific group like "Traders" above was found) will be triggert if any group was assigned to user


