<?php

return [
    'host' => 'host.mailer.tld',
    'username' => 'account@domain.tld',
    'password' => 'account.domain.tld.password',
    'secure' => 'ssl',
    'from' => 'sender.account@domain.tld',
    'dkim' => [
        'selector' => 'selector',
        'domain' => 'example.fr',
        'passphrase' => 'testtest',
        'privateKey' => '-----BEGIN RSA PRIVATE KEY-----
Proc-Type: 4,ENCRYPTED
DEK-Info: DES-EDE3-CBC,B473DD1445F5FCD6

rRWa7rd11Qh3w1sKge5okp8KhZwx+a6HOdgrgt5WpMq4G4oVrcBB74cx3ZzyJyTM
vjimQKjPKgyRXya1gdISUBl0WU6TySa8Z0ujL46KBvMJSy2UHQG/VZ24zqYgShiZ
lvEO+VyuZheIOCgK+aA7SC0PaLxlA+sq9uY6cZ8o4B8V1XaomTjnYi0GAQfji4b2
bee85lDzliNTTI9zj4wA5tt/C0rYkEKyBHI59hxHggat150SOIGWXplScLnUWA0f
j6XaPaMcZ5WuPL1lO46v95hVFct6mLWySduw1rdSTvK78d4BmtcgXDrq31OuzFQ3
CsFDSGMGDimg8fHXgo4Q2a8e3UVyL1XNi4qmEdQxfbAFUUdJsDVUixuwad1B7WK8
8Co/LME9AtSOSDTQN1hopaiXxkoJZfJED0j+o4GqKmkS5QvYj/Ri+S2M3e8M26kL
d7ZhYtVpwPZ0gIJvXcVm5deF3w4dNAvL3VjBDx4R/KkFvcLY3YFSVKZPIYHeLfYx
m9p+HYAzqEKeVz4T+p2/ph3XZCHvy1PMyoBKft4BNrGYloW3GxTWAkSE80RCwaJw
2x59MGqj8m1/po5HI/oUv+bJ/0tNgqn7Xzld1IvSxY0ikU+2jklMXWouY9a7k2wK
kfiDOa4jGlqUWsiq/0MQCGD8awdE57Q5rsWhpN8/0SRwSGC1qmmPE3LRqA48B2lX
SgJ3Se0i+umhciIi4DrEa94UpofibwqTniFv+exLZ4xW6RYkvZYhiQONl4XPBsVX
sKPE2t+JRPsWICm2wk2RaaKGmdxryAg+UHTNbDVPs45sogsKPYotzA==
-----END RSA PRIVATE KEY-----'
    ]
];
