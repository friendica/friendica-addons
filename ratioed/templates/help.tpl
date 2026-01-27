<div class="panel 'help-content-wrapper">
  <div class="panel-body">
    <h2>Ratioed Plugin Help</h2>
    <p>
      This plugin provides moderators with additional statistics about
      the behaviour of users.  These may be useful as early warning signs
      that warrant more carefully watching the behaviour of a user.  They
      are <em>not</em> suitable as a trigger for instantly blocking,
      muting, or reporting a user, since they lack context.
    </p>
    <p>
      The name of the plugin comes
      from <a href="https://knowyourmeme.com/editorials/guides/what-is-the-ratio-and-what-does-it-mean-to-get-ratioed-twitters-1-rule-explained">"The
        Ratio"</a>, a well-known quick rule of thumb:
    </p>
    <blockquote>
      If the Replies:RT ratio is greater than 2:1, you done messed up.
    </blockquote>
    <p>
      To "get ratioed" is to receive a large number of comments in a short
      space of time, with relatively few likes or boosts.  If commenters
      were enthusiastic about the posts, they would also have liked or
      boosted them.  Receiving many comments without such likes or boosts
      indicates the comments were probably angry.  This anger may or may
      not be justified, but either way this is probably something
      moderators should be aware of.
    </p>
    <p>
      This plugin allows viewing of an actual ratio, calculated over the
      last 24 hours.  This is a useful timeframe for sudden dogpiling
      events that moderators might not otherwise notice.  The plugin
      also calculates other statistics.
    </p>
    <h3>Explanation of Statistics</h3>
    <h4>Blocked by</h4>
    <p>
      This summarises the number of users on remote servers that have
      blocked this user.
    </p>
    <p>
      Note that the ActivityPub spec expressly says that
      implementations "SHOULD NOT" forward such block messages to
      remote servers.  Nevertheless some implementations do this
      anyway, notably Mastodon.  This statistic can only count block
      messages from servers that do this, as well as blocks from local
      users.  As such, it is usually an undercount.
    </p>
    <p>
      The reason the spec recommends against forwarding these messages
      is that they can lead to retaliation.  For this reason, this
      plugin deliberately does not provide any way to investigate
      exactly who blocked the user.
    </p>
    <h4>Comments last 24h</h4>
    <p>
      This gives the number of comments made on the top-level posts that
      this user made within the last 24 hours.
    </p>
    <h4>Reactions last 24h</h4>
    <p>
      This collects the number of likes, boosts, or other "one-click"
      interactions made on the user's top-level posts within the last 24
      hours.
    </p>
    <h4>Ratio last 24h</h4>
    <p>
      This is the ratio between "Comments last 24h" and "Reactions last
      24h".  It is intended to approximate the traditional ratio as
      understood on Twitter.
    </p>
    <h4>Replies last month</h4>
    <p>
      This is the number of times the user posted a reply to someone
      else, on a thread the user did not start, any time in the last
      month.
    </p>
    <h4>Reply likes</h4>
    <p>
      This is the number of likes received by the user on their
      replies to other people's posts in the last month.  Replies that
      receive likes can be assumed to be more of a valuable
      contribution than replies that do not.
    </p>
    <h4>Respondee likes</h4>
    <p>
      The number of times in the last month the user replied to
      someone else's comment and that person then liked the reply.
      Likes to replies are not necessarily a positive thing, but if
      the person you're replying to approves the reply, that's a very
      good sign.  Of course it's also common in a debate for neither
      side to like the other side's comments without that indicating
      an unhealthy interaction, so interpret this statistic cautiously.
    </p>
    <h4>OP likes</h4>
    <p>
      The number of times in the last month the user replied on a
      thread and the original poster that started the thread liked the
      reply.  While there is no formal concept of "ownership" of a
      thread, conventionally the original poster is assumed to have
      started the thread for a reason, and making replies that do not
      fulfil that purpose are bad etiquette.  Getting approval from
      the original poster therefore is a good sign that the user is
      posting replies that are wanted.
    </p>
    <h4>Reply guy score</h4>
    <p>
      A <a href="https://en.wikipedia.org/wiki/Reply_guy">"reply
      guy"</a> is a common Internet phenomenon of people
      (disproportionately male) posting unwanted comments on other
      (disproportionately female) people's threads, derailing the
      conversation.  This score loosely quantifies this phenomenon,
      as the ratio betwen the number of replies and the sum of likes,
      respondee likes, and OP likes.  This formula gives extra weight
      to particularly relevant likes: a reply to a top-level post that
      is liked by the original poster scores the maximum of 3
      "points".  A score above 1.0 might indicate cause for concern
      for moderators.
    </p>
    <p>
      Since this is indicative of long-term behaviour, the score is
      calculated over a month instead of 24 hours.
    </p>
    </p>
    <h3>Performance</h3>
    <p>
      The statistics are computed from scratch each time the page loads.
      It's possible that this might put a heavy load on the database, and
      the page may take a long time to load.
    </p>
    <h3>Extending</h3>
    <p>
      Suggestions for additional statistics are welcome, especially from
      moderators.  This plugin should be considered a sandbox for
      experimentation, so it is not necessary to prove that any statistic
      is correlated with unwanted behaviour.
    </p>
    <p>
      However, this plugin does deal with potentially sensitive
      information.  Even if moderators do in principle have access to all
      information, it should not necessarily be highlighted.  Statistics
      should be kept anonymous and neutral.  Also, they should be
      presented only to moderators, not to the users themselves.
    </p>
  </div>
</div>
