describe('publisher index page', function () {
  it('should be shown if a user is signed in as publisher', function () {
    browser.get('/');
    element(by.id('signin-link')).click();
    element(by.name('email')).sendKeys('bangodrop2@gmail.com');
    element(by.name('password')).sendKeys('test321');
    element(by.id('signin-button')).click();
    browser.sleep(1000);
    expect(element(by.tagName('h1')).getText()).toBe('Новая рекламная площадка');
  });

  it('should have navigation bar with 4 links', function () {
    browser.get('/#/');
    var list = element.all(by.css('.navbar-nav li'));
    expect(list.get(0).getText()).toBe('Новая площадка');
    expect(list.get(1).getText()).toBe('Мои площадки');
    expect(list.get(2).getText()).toBe('Доступные кампании');
    expect(list.get(3).getText()).toBe('Финансы');
  });

  it('should not be shown if a user is signed out', function () {
    element(by.id('signout-link')).click();
    browser.sleep(1000);
    expect(element(by.tagName('h1')).getText()).toBe('Как это работает');
  });
});
