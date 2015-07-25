describe('campaign form page', function () {
  it('should be shown if a user is signed in and tries to create a campaign', function () {
    browser.get('/');
    element(by.id('signin-link')).click();
    element(by.name('email')).sendKeys('test@mail.com');
    element(by.name('password')).sendKeys('test321');
    element(by.id('signin-button')).click();
    browser.sleep(500);
    browser.get('/#/campaign/new');
    var campaignInput = element(by.id('campaign-link'));
    campaignInput.clear();
    campaignInput.sendKeys('http://www.youtube.com/watch?v=WpkDN78P884');
    element(by.id('create-campaign-button')).click();
    expect(browser.getCurrentUrl()).toContain('campaign/create');
  });

  it('should have 6 age ranges', function () {
    browser.get('/#/campaign/create/WpkDN78P884');
    var ages = element.all(by.repeater('age in ages'));
    expect(ages.count()).toBe(6);
  });

  it('should not be shown if a user is signed out', function () {
    element(by.id('signout-link')).click();
    browser.sleep(1000);
    expect(element(by.tagName('h1')).getText()).toBe('Как это работает');
  });
});
