#!/bin/bash
openssl genrsa -des3 -out private.pem 1024
openssl rsa -in private.pem -out public.pem -outform PEM -pubout