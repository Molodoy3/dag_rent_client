const Ziggy = {"url":"http:\/\/localhost","port":null,"defaults":{},"routes":{"users.login":{"uri":"login","methods":["GET","HEAD"]},"users.store":{"uri":"login","methods":["POST"]},"user.show":{"uri":"users\/{id}","methods":["GET","HEAD"],"parameters":["id"]},"password.edit":{"uri":"edit-password","methods":["GET","HEAD"]},"password.update":{"uri":"update-password","methods":["POST"]},"accounts.index":{"uri":"accounts","methods":["GET","HEAD"]},"accounts.create":{"uri":"accounts\/create","methods":["GET","HEAD"]},"accounts.store":{"uri":"accounts","methods":["POST"]},"accounts.show":{"uri":"accounts\/{account}","methods":["GET","HEAD"],"parameters":["account"],"bindings":{"account":"id"}},"accounts.edit":{"uri":"accounts\/{account}\/edit","methods":["GET","HEAD"],"parameters":["account"],"bindings":{"account":"id"}},"accounts.update":{"uri":"accounts\/{account}","methods":["PUT","PATCH"],"parameters":["account"]},"accounts.destroy":{"uri":"accounts\/{account}","methods":["DELETE"],"parameters":["account"],"bindings":{"account":"id"}},"statistics.index":{"uri":"statistics","methods":["GET","HEAD"]},"statistics.create":{"uri":"statistics\/create","methods":["GET","HEAD"]},"statistics.store":{"uri":"statistics","methods":["POST"]},"statistics.show":{"uri":"statistics\/{statistic}","methods":["GET","HEAD"],"parameters":["statistic"]},"statistics.edit":{"uri":"statistics\/{statistic}\/edit","methods":["GET","HEAD"],"parameters":["statistic"],"bindings":{"statistic":"id"}},"statistics.update":{"uri":"statistics\/{statistic}","methods":["PUT","PATCH"],"parameters":["statistic"]},"statistics.destroy":{"uri":"statistics\/{statistic}","methods":["DELETE"],"parameters":["statistic"],"bindings":{"statistic":"id"}}}};
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
  Object.assign(Ziggy.routes, window.Ziggy.routes);
}
export { Ziggy };
