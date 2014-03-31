ssh-pubkey-encrypter
==========================

Encode short text(<=100byte. ex: zip password) by ssh public RSA key. Receiver can decode by own openssl command with pair private key.

Oh, Don't you know friend's ssh-rsa key? no problem, Github disclose ssh public RSA key in `https://github.com/uzulla.keys`, that's very usefull. this tool can pick ssh-rsa key from that.


Githubなどで公開されているユーザーのssh公開鍵（RSAのみ）をつかって、短いテキストを暗号化できます。復号は対応する秘密鍵を使います。
システムに入っているopensslコマンドで複合する事も可能です。

ssh-rsa鍵を相手から聞くのが面倒…とおもうでしょうが、Githubは`https://github.com/uzulla.keys`などのURLで公開鍵を公開しています。このツールはそこから鍵をひっぱってこれます、便利。

とりあえず短いテキストしか暗号化できませんが、「ZIPパスワードは別メールで送ります」がいい加減アレなのでつくりました。

# require

- PHP>=5.3(maybe, developing in 5.5)

# install

```
# install composer
$ url -sS https://getcomposer.org/installer | php
# install deps
$ php composer.phar install

$ ./ssh-pubkey-encode --help
```

# how to QUICK use.

## encode

```
$ ./ssh-pubkey-encode -k github_user_name:0
TYPE AS YOU WANT(enter)
(^D)
APA(SNIP!)GA=
```

-iや-oを省略すると、標準入出力を使います。

`$ echo 'hoge'|./ssh-pubkey-encode〜`も勿論つかえます


### select public key `-k` option

`-k`で公開鍵を指定します、Githubユーザー名や、ファイル名や、直接公開鍵を文字列で指定できます。
Githubユーザー名を指定すると、`http://github.com/uzulla.keys`などから取得します。複数公開鍵を登録している人もいるので、何行目か指定してください（0行目からです）。

```
GitHub: -k 'uzulla:0'. Get pubkey from github https://github.com/user_name.keys  , :0 is row num
direct key: -k 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQ(snip)'
file path: -k '~/.ssh/id_rsa.pub'
```

## decode

```
$ ./ssh-privkey-decode
please type pass phrase (or blank) :
paste data and Enter,^D
APA(SNIP!)GA=(enter)
(^D)
TYPE AS YOU WANT
```

いきなり起動して、パスフレーズを入力すると、入力をうけつける状態になりますので、データをペーストし、Enterし、`^D`することでデコードされます。

-iや-oを省略すると、標準入出力を使います。

-kを省略すると`~/.ssh/id_rsa`を指定したのと同じになります。


# how to strict use

```
# create text.
$ echo "secret text" > plain.txt

# encode text
$ ./ssh-pubkey-encode -k github_user_name:0 -i plain.txt -o encode.txt

$ cat encode.txt
Ro1SgTGRw6uSq6yBZ0i
(snip)
oiTBKycbr/WWDBhSw1I=

# decode text
$ ./ssh-privkey-decode -k ~/.ssh/id_rsa -i encode.txt -o decode.txt
please type pass phrase (or blank) : (type your pass phrase)

$ cat decode.txt
secret text
```


# DON'T USE INSANE OPTION

```
$ cat encoded.txt | ./ssh-privkey-decode --i_want_set_pass_phrase_option_i_am_sane 'mysecretpassphraseqazqwe1234'
secret text
```

# Don't support DSA key

DSAの鍵はサポートしていません。

# Do you trust PHP (or this code)?

このコードが信用できない以前に、PHPとか信用できない人に朗報です。システムに最初から入っているopensslコマンドで展開できます。

you can decode your openssl!!!

```
$ cat encode.txt |base64 -D | openssl rsautl -decrypt -inkey ~/.ssh/id_rsa
```

# other option

see `ssh-pubkey-encode --help`

```
$ ssh-pubkey-encode --help
 ./ssh-pubkey-encode

-r/--raw/--binary
     Don't base64 encode

-i/--infile <argument>
     input_plain_file:max 100 byte

-k/--pubkey <argument>
     Required. ex:
         GitHub: -k 'uzulla:0'. Get pubkey from github https://github.com/user_name.keys  , :0 is row num
         direct key: -k 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQ(snip)'
         file path: -k '~/.ssh/id_rsa.pub'

-o/--outfile <argument>
     output encoded file
```
