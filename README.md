# Naija Emoji RESTful API 😎
  
A RESTful emoji service that lets you add, find, update and delete emojis with authentication

[![Build Status](https://travis-ci.org/andela-jkariuki/checkpoint-three-naija-emoji.svg?branch=master)](https://travis-ci.org/andela-jkariuki/checkpoint-three-naija-emoji)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/andela-jkariuki/checkpoint-three-naija-emoji/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/andela-jkariuki/checkpoint-three-naija-emoji/?branch=master)
[![Software License][ico-license]](https://github.com/andela-jkariuki/checkpoint-three-naija-emoji)

**TIA**

###API Root

The Naija Emoji APi is accesible throgh the url 
```bash
https://naijaemoji-staging.herokuapp.com
```

The default root returns the following JSON message.
```json
{
  "message": "welcome to the naija-emoji RESTful Api"
}
```

Missing required fields return 
```json
{
  "message": "Missing some required fields"
}
```

**Attach the token as a header with key `token` to authenticated routes**

Invalid authenticated paths (without token header) returns
```json
{
  "message": "No token provided."
}
```

Invalid authenticated paths (with invalid token) returns
```json
{
  "message": "invalid token."
}
```

###Auth Register User | POST /auth/register
Pass a username, and password to `https://naijaemoji-staging.herokuapp.com/auth/register`

A succesful registration returns
```json
{
  "message": "User successfully registered."
}
```

Double registration will return
```json
{
  "message": "User already exists."
}
```

###Auth Register User | POST /auth/login

Pass a username, and password to `https://naijaemoji-staging.herokuapp.com/auth/login`

succesful authentication returns a success message and 24 hour token
```json
{
  "message": "login successful",
  "token": "313064bdcbf72cbf050e0c5bfae44a91682fd42fa1e88df705b1f183a94b6580"
}
```
**The token will be passed as a header for all authenticated tokens**

Login with invalid credentials returns an error message
```json
{
  "message": "Invalid login credentials."
}
```

###Auth Logout user | GET /auth/logout

Route:  `https://naijaemoji-staging.herokuapp.com/auth/logout`
Succesful logout returns
```json
{
  "message": "successfully logged out."
}
```

###Add new emoji | POST /emojis

Route: `https://naijaemoji-staging.herokuapp.com/emojis`
Successfully added emoji returns

```json
{
  "message": "Emoji added succesfully."
}
```

###Get all emojis | GET /emojis

Route: `https://naijaemoji-staging.herokuapp.com/emojis`
Succesfully fetched emojis returns
```json
[
  {
    "id": 119,
    "name": "innocent",
    "char": "😇 ",
    "keywords": "['happy',' holy',' angel',' sweet',' awww',' innocent']",
    "category": "person",
    "date_created": "2016-03-02 18:44:43",
    "date_modified": "2016-03-02 18:44:43",
    "created_by": "doris.carter"
  },
  {
    "id": 99,
    "name": "bikini",
    "char": "👙",
    "keywords": "['bikini',' sexy swim']",
    "category": "clothes",
    "date_created": "2016-03-02 08:09:49",
    "date_modified": "2016-03-02 08:09:49",
    "created_by": "ganga"
  }
]
```

No emojis fetched returns
```json
{
  "message": "no emoji found"
}
```

###Get One emoji | GET /emojis/{id}

Route: `https://naijaemoji-staging.herokuapp.com/emojis/45`
One fetched emoji returns

```json
{
  "id": 45,
  "name": "arms",
  "char": "💪",
  "keywords": "['strong','arm','weider']",
  "category": "arms",
  "date_created": "2016-03-01 06:54:02",
  "date_modified": "2016-03-01 07:04:36",
  "created_by": "Kianna64"
}
```

No fetched emoji returns
```json
{
  "message": "no emoji found"
}
```

###Update Emoji | PATCH /emojis/{id}

Route: `https://naijaemoji-staging.herokuapp.com/emojis/45`

Note that PUT overwrites the database record, therefore, all required fields (`name, char, keywords, category`) must be provided.

PATCH updates only specified fields.

a succesful update returns
```json
{
  "message": "Emoji updated succesfully."
}
```

Updating a non existent emoji returns
```json
{
  "message": "No record found with that ID."
}
```

###Delete emoji | DELETE /emojis/{id}

Route: `https://naijaemoji-staging.herokuapp.com/emojis/45`

A succesful delete returns 
```json
{
  "message": "Emoji deleted succesfully."
}
```

A failed delete returns: 
```json
{
  "message": "Error deleting emoji."
}
```

###Search By field GET /emojis/{field}/{name}
Route: `https://naijaemoji-staging.herokuapp.com/emojis/created_by/oscar`

Non empty emojis array found returns
```json
[
  {
    "id": 87,
    "name": "sun_glasses",
    "char": "😎",
    "keywords": "['sun\",'glasses','cool']",
    "category": "person",
    "date_created": "2016-03-01 18:09:10",
    "date_modified": "2016-03-01 18:09:10",
    "created_by": "oscar"
  }
]
```

Empty JSON array returns
```json
{
  "message": "no emojis found whose created_by field is  john"
}
```

## Contributing

Contributions are **welcome** and will be fully **credited**.

We accept contributions via Pull Requests on [Github](https://github.com/andela-jkariuki/checkpoint-three-naija-emoji).

## Pull Requests

- **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - The easiest way to apply the conventions is to install [PHP Code Sniffer](http://pear.php.net/package/PHP_CodeSniffer).

- **Add tests!** - Your patch won't be accepted if it doesn't have tests.

- **Document any change in behaviour** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [SemVer v2.0.0](http://semver.org/). Randomly breaking public APIs is not an option.

- **Create feature branches** - Don't ask us to pull from your master branch.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](http://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting.

## Security

If you discover any security related issues, please email [John Kariuki](john.kariuki@andela.com) or create an issue.

## Credits

[John kariuki](https://github.com/andela-jkariuki)

## License

### The MIT License (MIT)

Copyright (c) 2016 John kariuki <john.kariuki@andela.com>

> Permission is hereby granted, free of charge, to any person obtaining a copy
> of this software and associated documentation files (the "Software"), to deal
> in the Software without restriction, including without limitation the rights
> to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
> copies of the Software, and to permit persons to whom the Software is
> furnished to do so, subject to the following conditions:
>
> The above copyright notice and this permission notice shall be included in
> all copies or substantial portions of the Software.
>
> THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
> IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
> FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
> AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
> LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
> OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
> THE SOFTWARE.

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
