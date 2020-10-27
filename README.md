config/jwt files
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 (passphrase 1234)
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout

разобраться в env и разбить их