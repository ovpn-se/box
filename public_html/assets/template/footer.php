</div>
</div>
</div>
</div>
<div class="already-account">
    <?php if(!\Base\User::getCredentials()) { ?> <a href="https://www.ovpn.se/account/create/">Skapa ett konto</a> | <a href="https://www.ovpn.se/account/forgot/">Återställ lösenordet</a> <?php } else { ?> <a href="/logout/">Logga ut</a> <?php } ?>
</div>
</div>
</div>
</div>
</body>
</html>