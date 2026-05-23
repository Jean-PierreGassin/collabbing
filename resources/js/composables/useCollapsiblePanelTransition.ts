function resetPanelStyles(element: HTMLElement): void {
  element.style.height = '';
  element.style.opacity = '';
  element.style.overflow = '';
  element.style.transform = '';
  element.style.transition = '';
  element.style.willChange = '';
}

function finishPanelTransition(
  panel: HTMLElement,
  propertyName: string,
  done: () => void,
  fallbackDelay: number,
): void {
  let isFinished = false;
  const timer = window.setTimeout(finish, fallbackDelay);

  function finish(): void {
    if (isFinished) {
      return;
    }

    isFinished = true;
    window.clearTimeout(timer);
    panel.removeEventListener('transitionend', handleTransitionEnd);
    resetPanelStyles(panel);
    done();
  }

  function handleTransitionEnd(event: TransitionEvent): void {
    if (event.target === panel && event.propertyName === propertyName) {
      finish();
    }
  }

  panel.addEventListener('transitionend', handleTransitionEnd);
}

export function useCollapsiblePanelTransition() {
  function beforeEnter(element: Element): void {
    const panel = element as HTMLElement;

    panel.style.height = '0';
    panel.style.opacity = '0';
    panel.style.overflow = 'hidden';
    panel.style.transform = 'translateY(-0.35rem)';
    panel.style.willChange = 'height, opacity, transform';
  }

  function enter(element: Element, done: () => void): void {
    const panel = element as HTMLElement;

    panel.style.transition = 'height 240ms ease, opacity 220ms ease, transform 220ms ease';

    requestAnimationFrame(() => {
      panel.style.height = `${panel.scrollHeight}px`;
      panel.style.opacity = '1';
      panel.style.transform = 'translateY(0)';
    });

    finishPanelTransition(panel, 'height', done, 320);
  }

  function leave(element: Element, done: () => void): void {
    const panel = element as HTMLElement;

    panel.style.height = `${panel.scrollHeight}px`;
    panel.style.opacity = '1';
    panel.style.overflow = 'hidden';
    panel.style.transform = 'translateY(0)';
    panel.style.transition = 'height 220ms ease, opacity 180ms ease, transform 180ms ease';

    requestAnimationFrame(() => {
      panel.style.height = '0';
      panel.style.opacity = '0';
      panel.style.transform = 'translateY(-0.25rem)';
    });

    finishPanelTransition(panel, 'height', done, 300);
  }

  return {
    beforeEnter,
    enter,
    leave,
  };
}
