describe("publisher's wallet page", function () {
  it('should be not shown if a user is signed in and goes on the wallet page', function () {
    browser.get('/');
    element(by.id('signin-link')).click();
    element(by.name('email')).sendKeys('bangodrop2@gmail.com');
    element(by.name('password')).sendKeys('test321');
    element(by.id('signin-button')).click();
    browser.sleep(1000);
    browser.get('/#/finance/wallet');
    expect(element(by.tagName('h1')).getText()).toBe('Управление кошельками');
  });

  it('should display a list of wallets', function () {
    browser.get('/#/finance/wallet/list');
    var headers = element.all(by.css('thead th'));
    var list = element.all(by.css('tbody tr'));
    expect(headers.count()).toBe(5);
    expect(list.count()).toBeGreaterThan(0);
  });

  it('should have "Add wallet" button', function () {
    browser.get('/#/finance/wallet/list');
    expect(element(by.id('add-wallet-button')).getText()).toBe('Добавить кошелек');
  });

  it('should move on new wallet page when "Add wallet" pressed', function () {
    browser.get('/#/finance/wallet/list');
    element(by.id('add-wallet-button')).click();
    expect(element(by.tagName('h1')).getText()).toBe('Новый кошелек');
  });

  it('should display error if a user tries to add empty number', function () {
    browser.get('/#/finance/wallet/new');
    element(by.id('add-wallet-button')).click();
    expect(element(by.css('.help-block')).getText()).toBe('Вы забыли указать номер кошелька');
  });

  it('should display error if a user tries to add invalid Yandex wallet', function () {
    browser.get('/#/finance/wallet/new');
    element(by.id('wallet-number')).sendKeys('wrong value');
    element(by.id('add-wallet-button')).click();
    expect(element(by.css('.help-block')).getText()).toContain('Похоже, что вы указали неверный номер кошелька. Номер Я.Деньги');
  });

  it('should display error if a user tries to add invalid WM wallet', function () {
    browser.get('/#/finance/wallet/new');
    element(by.id('wallet-type')).click();
    element(by.css('.dropdown-menu li:nth-child(2) a')).click();
    element(by.id('wallet-number')).sendKeys('wrong value');
    element(by.id('add-wallet-button')).click();
    expect(element(by.css('.help-block')).getText()).toContain('Похоже, что вы указали неверный номер кошелька. Номер WM');
  });

  it('should display error if a user tries to add invalid Qiwi wallet', function () {
    browser.get('/#/finance/wallet/new');
    element(by.id('wallet-type')).click();
    element(by.css('.dropdown-menu li:nth-child(3) a')).click();
    element(by.id('wallet-number')).sendKeys('wrong value');
    element(by.id('add-wallet-button')).click();
    expect(element(by.css('.help-block')).getText()).toContain('Похоже, что вы указали неверный номер кошелька. Номер Qiwi');
  });

  it('should not be shown if a user is signed out', function () {
    element(by.id('signout-link')).click();
    browser.sleep(1000);
    expect(element(by.tagName('h1')).getText()).toBe('Как это работает');
  });
});
