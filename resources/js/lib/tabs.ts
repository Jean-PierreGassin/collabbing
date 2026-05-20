import { nextTick, ref } from 'vue';
import type { Ref } from 'vue';

export interface AccessibleTab<T extends string> {
  value: T;
  tabId: string;
  panelId: string;
}

export function useAccessibleTabs<T extends string>(
  tabs: readonly AccessibleTab<T>[],
  initialTab: T,
) {
  const activeTab = ref(initialTab) as Ref<T>;

  function tabDefinition(tab: T): AccessibleTab<T> {
    return tabs.find((definition) => definition.value === tab) ?? tabs[0];
  }

  function selectTab(tab: T): void {
    activeTab.value = tab;
  }

  function isSelected(tab: T): boolean {
    return activeTab.value === tab;
  }

  function tabIndex(tab: T): 0 | -1 {
    if (isSelected(tab)) {
      return 0;
    }

    return -1;
  }

  async function focusTab(tab: T): Promise<void> {
    await nextTick();
    document.getElementById(tabDefinition(tab).tabId)?.focus();
  }

  function adjacentTab(offset: number): T {
    const currentIndex = tabs.findIndex((tab) => tab.value === activeTab.value);
    const activeIndex = currentIndex >= 0 ? currentIndex : 0;
    const nextIndex = (activeIndex + offset + tabs.length) % tabs.length;

    return tabs[nextIndex].value;
  }

  function tabForKey(key: string): T | null {
    if (key === 'ArrowRight' || key === 'ArrowDown') {
      return adjacentTab(1);
    }

    if (key === 'ArrowLeft' || key === 'ArrowUp') {
      return adjacentTab(-1);
    }

    if (key === 'Home') {
      return tabs[0].value;
    }

    if (key === 'End') {
      return tabs[tabs.length - 1].value;
    }

    return null;
  }

  function handleTabKeydown(event: KeyboardEvent): void {
    const nextTab = tabForKey(event.key);

    if (!nextTab) {
      return;
    }

    event.preventDefault();
    selectTab(nextTab);
    void focusTab(nextTab);
  }

  return {
    activeTab,
    handleTabKeydown,
    isSelected,
    selectTab,
    tabIndex,
  };
}
