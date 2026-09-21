#!/bin/bash
exec socat OPENSSL-LISTEN:8445,cert=/home/alfin/latekaje-v3/ssl-v2/v2.pem,verify=0,fork TCP:127.0.0.1:8002
